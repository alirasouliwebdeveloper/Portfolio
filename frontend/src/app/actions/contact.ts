"use server";

import { headers } from "next/headers";
import { contactSchema, type ContactState } from "@/lib/contact-schema";

/**
 * Forwards the contact form to Laravel. Runs on the server only, so the internal key never
 * reaches the browser; the visitor IP and user agent are passed along for rate limiting and
 * Cloudflare Turnstile verification.
 */
export async function submitContact(
  _previous: ContactState,
  formData: FormData,
): Promise<ContactState> {
  const raw = Object.fromEntries(
    [
      "name",
      "email",
      "company",
      "phone",
      "need",
      "budget",
      "timeline",
      "message",
      "service_slug",
      "upload_session",
    ].map((key) => {
      const value = formData.get(key);
      return [
        key,
        typeof value === "string" && value !== "" ? value : undefined,
      ];
    }),
  );
  const uploads = formData
    .getAll("upload_uuids")
    .filter(
      (value): value is string => typeof value === "string" && value !== "",
    );

  const parsed = contactSchema.safeParse({ ...raw, upload_uuids: uploads });
  if (!parsed.success) {
    const fieldErrors: NonNullable<ContactState["fieldErrors"]> = {};
    for (const issue of parsed.error.issues) {
      const field = issue.path[0] as keyof NonNullable<
        ContactState["fieldErrors"]
      >;
      fieldErrors[field] ??= issue.message;
    }
    return {
      status: "error",
      message: "Please fix the highlighted fields.",
      fieldErrors,
    };
  }

  const turnstile = formData.get("cf-turnstile-response");
  const requestHeaders = await headers();
  const ip =
    requestHeaders.get("x-forwarded-for")?.split(",")[0]?.trim() ??
    requestHeaders.get("x-real-ip") ??
    "";

  let response: Response;
  try {
    response = await fetch(`${process.env.API_URL}/contact`, {
      method: "POST",
      cache: "no-store",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-Internal-Key": process.env.API_INTERNAL_KEY ?? "",
      },
      body: JSON.stringify({
        ...parsed.data,
        turnstile_token: typeof turnstile === "string" ? turnstile : null,
        visitor_ip: ip,
        user_agent: requestHeaders.get("user-agent") ?? "",
      }),
    });
  } catch {
    return {
      status: "error",
      message:
        "Something went wrong on our side. Please try again in a moment.",
    };
  }

  if (response.status === 422) {
    const body = (await response.json()) as {
      message?: string;
      errors?: Record<string, string[]>;
    };
    const fieldErrors: NonNullable<ContactState["fieldErrors"]> = {};
    for (const [field, messages] of Object.entries(body.errors ?? {})) {
      fieldErrors[field as keyof NonNullable<ContactState["fieldErrors"]>] =
        messages[0];
    }
    return {
      status: "error",
      message: body.message ?? "Please fix the highlighted fields.",
      fieldErrors,
    };
  }

  if (response.status === 429) {
    return {
      status: "error",
      message:
        "Too many messages from your connection. Please try again later.",
    };
  }

  if (!response.ok) {
    return {
      status: "error",
      message:
        "Something went wrong on our side. Please try again in a moment.",
    };
  }

  return { status: "success", name: parsed.data.name };
}
