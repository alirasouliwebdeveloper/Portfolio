"use client";

import {
  useActionState,
  useCallback,
  useEffect,
  useRef,
  useState,
  startTransition,
  type FormEvent,
} from "react";
import { useSearchParams } from "next/navigation";
import { submitContact } from "@/app/actions/contact";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { Icon } from "@/components/ui/icons";
import { IconTile } from "@/components/ui/IconTile";
import { Input } from "@/components/ui/Input";
import { RadioPillGroup } from "@/components/ui/RadioPill";
import { Select } from "@/components/ui/Select";
import { Textarea } from "@/components/ui/Textarea";
import type { ContactState } from "@/lib/contact-schema";
import type { Settings } from "@/types/api";
import { Turnstile } from "./Turnstile";
import { Uploader } from "./Uploader";

const initial: ContactState = { status: "idle" };

const fieldLabels: Record<string, string> = {
  name: "Your name",
  email: "Email",
  company: "Company",
  phone: "Phone or WhatsApp",
  need: "What do you need?",
  budget: "Budget",
  timeline: "Timeline",
  message: "Project details",
  upload_uuids: "Attachments",
  turnstile: "Security check",
};

/** `?service=online-store-development` from a service page preselects the closest option. */
function needForService(slug: string | null, needs: string[]) {
  if (!slug) return undefined;
  const keyword = /store|shop/.test(slug)
    ? "store"
    : /api|backend/.test(slug)
      ? "api"
      : /automation|n8n/.test(slug)
        ? "automation"
        : /laravel|next|web/.test(slug)
          ? "website"
          : null;
  return keyword
    ? needs.find((need) => need.toLowerCase().includes(keyword))
    : undefined;
}

export function ContactForm({
  options,
}: {
  options: Settings["contact_options"];
}) {
  const [state, action, pending] = useActionState(submitContact, initial);
  const service = useSearchParams().get("service");
  const [uploading, setUploading] = useState(false);
  const [verifiedFor, setVerifiedFor] = useState<number | null>(null);
  const summary = useRef<HTMLDivElement>(null);
  const needsTurnstile = Boolean(process.env.NEXT_PUBLIC_TURNSTILE_SITE_KEY);

  // Turnstile tokens are single use: a new widget (new key) is created after every failed attempt.
  const widgetKey = state.nonce ?? 0;
  const verified = verifiedFor === widgetKey;
  const onVerify = useCallback(
    (value: boolean) => setVerifiedFor(value ? widgetKey : null),
    [widgetKey],
  );

  useEffect(() => {
    if (state.status === "error") summary.current?.focus();
  }, [state]);

  // Submitting through startTransition keeps what the visitor typed when the server reports errors.
  const onSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const data = new FormData(event.currentTarget);
    startTransition(() => action(data));
  };

  if (state.status === "success") {
    return (
      <Card
        radius="lg"
        className="flex flex-col items-center gap-4 py-16 text-center"
        aria-live="polite"
      >
        <IconTile name="check" tone="accent" size="lg" />
        <h2 className="text-card-title-lg text-text font-semibold">
          Thanks, {state.name} — I&apos;ll reply within one working day.
        </h2>
        <p className="text-body text-muted">
          A confirmation is on its way to your inbox.
        </p>
      </Card>
    );
  }

  const errors = state.fieldErrors ?? {};
  const errorEntries = Object.entries(errors).filter(([, message]) => message);

  return (
    <form onSubmit={onSubmit} noValidate>
      <Card
        radius="lg"
        className="tablet:p-8 desktop:p-12 flex flex-col gap-5.5 p-6"
      >
        {state.status === "error" ? (
          <div
            ref={summary}
            tabIndex={-1}
            role="alert"
            className="rounded-control border-danger-border text-caption text-danger focus-visible:outline-accent border px-4 py-3.5 outline-none focus-visible:outline-2"
          >
            <p className="font-medium">{state.message}</p>
            {errorEntries.length > 0 ? (
              <ul className="mt-2 list-disc ps-5">
                {errorEntries.map(([field, message]) => (
                  <li key={field}>
                    <a href={`#contact-${field}`} className="underline">
                      {fieldLabels[field] ?? field}
                    </a>
                    : {message}
                  </li>
                ))}
              </ul>
            ) : null}
          </div>
        ) : null}

        <div className="tablet:grid-cols-2 grid gap-5.5">
          <Input
            id="contact-name"
            name="name"
            label="Your name"
            autoComplete="name"
            required
            error={errors.name}
          />
          <Input
            id="contact-email"
            name="email"
            type="email"
            label="Email"
            autoComplete="email"
            required
            error={errors.email}
          />
        </div>
        <div className="tablet:grid-cols-2 grid gap-5.5">
          <Input
            id="contact-company"
            name="company"
            label="Company"
            optional
            autoComplete="organization"
            error={errors.company}
          />
          <Input
            id="contact-phone"
            name="phone"
            type="tel"
            label="Phone or WhatsApp"
            optional
            autoComplete="tel"
            error={errors.phone}
          />
        </div>

        <RadioPillGroup
          legend="What do you need?"
          name="need"
          options={options.needs}
          defaultValue={
            needForService(service, options.needs) ?? options.needs[0]
          }
          error={errors.need}
        />

        <div className="tablet:grid-cols-2 grid gap-5.5">
          <Select
            id="contact-budget"
            name="budget"
            label="Budget"
            options={options.budgets}
            error={errors.budget}
          />
          <Select
            id="contact-timeline"
            name="timeline"
            label="Timeline"
            options={options.timelines}
            error={errors.timeline}
          />
        </div>

        <Textarea
          id="contact-message"
          name="message"
          label="Project details"
          required
          error={errors.message}
        />
        {service ? (
          <input type="hidden" name="service_slug" value={service} />
        ) : null}

        <Uploader rules={options.upload} onBusyChange={setUploading} />
        {errors.upload_uuids ? (
          <p
            role="alert"
            id="contact-upload_uuids"
            className="text-caption text-danger"
          >
            {errors.upload_uuids}
          </p>
        ) : null}

        {needsTurnstile ? (
          <div className="flex flex-col gap-2.5">
            <span
              className="text-text-2 text-sm font-medium"
              id="contact-turnstile"
            >
              Security check
            </span>
            <Turnstile key={widgetKey} onVerify={onVerify} />
            {errors.turnstile ? (
              <p role="alert" className="text-caption text-danger">
                {errors.turnstile}
              </p>
            ) : null}
          </div>
        ) : null}

        <div className="tablet:flex-row tablet:items-center tablet:justify-between flex flex-col gap-5">
          <p className="text-caption text-dim">
            Your details are only used to reply to this message.
          </p>
          <Button
            type="submit"
            size="sm"
            disabled={pending || uploading || (needsTurnstile && !verified)}
          >
            {pending
              ? "Sending…"
              : uploading
                ? "Uploading files…"
                : "Send Message"}{" "}
            <Icon name="send" className="size-4 rtl:-scale-x-100" />
          </Button>
        </div>
      </Card>
    </form>
  );
}
