import { IconTile } from "@/components/ui/IconTile";
import type { ProcessStep } from "@/types/api";

/** Numbered step cards, shared by the About page and the service landings. */
export function ProcessSteps({ steps }: { steps: ProcessStep[] }) {
  return (
    <ol className="tablet:grid-cols-2 tablet:gap-6 desktop:grid-cols-4 grid gap-5">
      {steps.map((step, index) => (
        <li
          key={step.title}
          className="rounded-card border-border bg-surface p-card flex flex-col gap-3 border"
        >
          <div className="flex items-start justify-between">
            <IconTile name={step.icon} tone="tile" />
            <span className="text-caption text-link font-semibold">
              Step {index + 1}
            </span>
          </div>
          <h3 className="text-card-title text-text mt-1 font-semibold">
            {step.title}
          </h3>
          <p className="text-ui text-muted leading-relaxed">{step.text}</p>
        </li>
      ))}
    </ol>
  );
}
