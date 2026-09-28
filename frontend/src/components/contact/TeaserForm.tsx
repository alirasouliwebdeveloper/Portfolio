"use client";

import { useActionState, useCallback, useState } from "react";
import { submitContact } from "@/app/actions/contact";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { Input } from "@/components/ui/Input";
import { Select } from "@/components/ui/Select";
import { Textarea } from "@/components/ui/Textarea";
import { IconTile } from "@/components/ui/IconTile";
import { ArrowRight } from "@/components/ui/icons";
import type { ContactState } from "@/lib/contact-schema";
import { Turnstile } from "./Turnstile";

const initial: ContactState = { status: "idle" };

/** Short contact form on the home page. Same endpoint as the full form, without uploads. */
export function TeaserForm({ needs }: { needs: string[] }) {
  const [state, action, pending] = useActionState(submitContact, initial);
  const [verified, setVerified] = useState(false);
  const onVerify = useCallback((value: boolean) => setVerified(value), []);
  const needsTurnstile = Boolean(process.env.NEXT_PUBLIC_TURNSTILE_SITE_KEY);

  if (state.status === "success") {
    return (
      <Card
        radius="lg"
        className="flex flex-col items-center gap-4 py-12 text-center"
        aria-live="polite"
      >
        <IconTile name="check" tone="accent" />
        <h3 className="text-card-title-lg text-text font-semibold">
          Thanks, {state.name} — I&apos;ll reply within one working day.
        </h3>
      </Card>
    );
  }

  const errors = state.fieldErrors ?? {};

  return (
    <form action={action} noValidate>
      <Card radius="lg" className="flex flex-col gap-5">
        {state.status === "error" && state.message ? (
          <p
            role="alert"
            className="rounded-control border-danger-border text-caption text-danger border px-4 py-3"
          >
            {state.message}
          </p>
        ) : null}

        <div className="tablet:grid-cols-2 grid gap-5">
          <Input
            name="name"
            label="Your name"
            autoComplete="name"
            required
            error={errors.name}
          />
          <Input
            name="email"
            label="Email"
            type="email"
            autoComplete="email"
            required
            error={errors.email}
          />
        </div>
        <Select
          name="need"
          label="What do you need?"
          options={needs}
          error={errors.need}
        />
        <Textarea
          name="message"
          label="Project details"
          required
          error={errors.message}
        />
        {needsTurnstile ? (
          <Turnstile onVerify={onVerify} appearance="interaction-only" />
        ) : null}
        {errors.turnstile ? (
          <p role="alert" className="text-caption text-danger">
            {errors.turnstile}
          </p>
        ) : null}

        <div>
          <Button
            type="submit"
            disabled={pending || (needsTurnstile && !verified)}
          >
            {pending ? "Sending…" : "Send Message"}{" "}
            <ArrowRight className="size-4" />
          </Button>
        </div>
      </Card>
    </form>
  );
}
