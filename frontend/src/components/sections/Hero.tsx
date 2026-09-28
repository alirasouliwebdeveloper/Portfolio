import { Button } from "@/components/ui/Button";
import { Container } from "@/components/ui/Container";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { ArrowUpRight } from "@/components/ui/icons";
import { Media } from "@/components/ui/Media";
import { TechIcon } from "@/components/ui/brand-icons";
import type { HomeContent, ProjectCard, Settings } from "@/types/api";
import { Download } from "lucide-react";
import { CodeCard } from "./CodeCard";

type HeroProps = {
  content: HomeContent["hero"];
  title: string;
  settings: Settings;
  firstProject: ProjectCard | null;
};

export function Hero({ content, settings, firstProject }: HeroProps) {
  const { profile } = settings;

  return (
    <section
      aria-labelledby="hero-title"
      className="border-line-soft bg-bg tablet:pt-14 tablet:pb-16 desktop:pt-0 desktop:pb-0 border-b pt-10 pb-12"
    >
      <Container className="desktop:h-185 relative">
        <div className="desktop:absolute desktop:start-0 desktop:top-24 desktop:w-175 flex flex-col items-start">
          <Eyebrow variant="chip">{content.chip}</Eyebrow>
          <h1
            id="hero-title"
            className="text-hero-h1 tracking-h1 text-text mt-6 leading-[1.08]"
          >
            {content.greeting}{" "}
            <span className="text-accent-soft">{profile.name}</span>
          </h1>
          <p className="text-hero-sub tracking-h1 text-text mt-2.5 leading-[1.15] font-semibold">
            {profile.headline}
          </p>
          <p className="text-lead text-muted mt-5.5 max-w-140">
            {content.lead}
          </p>

          <div className="tablet:w-auto tablet:flex-row mt-9 flex w-full flex-col gap-4">
            <Button
              href={
                firstProject ? `/projects/${firstProject.slug}` : "/#projects"
              }
              fullWidth
              className="tablet:w-auto"
            >
              View My Work <ArrowUpRight className="size-3.5" />
            </Button>
            {profile.cv_url ? (
              <Button
                href={profile.cv_url}
                variant="outline"
                fullWidth
                className="tablet:w-auto"
                download
              >
                Download CV{" "}
                <Download
                  className="size-4.5"
                  strokeWidth={1.8}
                  aria-hidden="true"
                />
              </Button>
            ) : null}
          </div>

          <div className="tracking-eyebrow text-text-2 mt-13 text-xs font-semibold uppercase">
            {content.tech_label}
          </div>
          <ul className="tablet:gap-6.5 desktop:gap-7.5 mt-4.5 flex flex-wrap items-center gap-5">
            {content.technologies.map((name) => (
              <li key={name}>
                <TechIcon name={name} className="tablet:size-10.5 size-8.5" />
              </li>
            ))}
          </ul>
        </div>

        <div className="tablet:mt-14 tablet:h-133.5 desktop:absolute desktop:end-0 desktop:top-27.5 desktop:mt-0 desktop:block desktop:h-147.5 desktop:w-120 relative mt-12 flex h-97.5 justify-center">
          <DotGrid className="desktop:block absolute end-0 -top-8 hidden" />
          <CurvedArrow className="desktop:block absolute -start-40 top-45 hidden" />

          <div className="portrait-arch bg-portrait tablet:h-126 desktop:h-147.5 relative h-90 overflow-hidden">
            {profile.portrait ? (
              <div className="absolute start-2.5 bottom-0 h-[95%] w-[calc(100%-0.625rem)]">
                <Media
                  image={profile.portrait}
                  size="lg"
                  fill
                  priority
                  sizes="(min-width: 1280px) 480px, (min-width: 768px) 420px, 300px"
                  className="object-top"
                />
              </div>
            ) : null}
          </div>

          <CodeCard
            name={profile.name}
            stack={content.code_stack}
            passion={content.code_passion}
            className="tablet:start-10 tablet:w-62.5 desktop:-start-42.5 desktop:bottom-auto desktop:top-97.5 desktop:w-65 absolute start-0 bottom-0 w-57.5"
          />
        </div>
      </Container>
    </section>
  );
}

function DotGrid({ className }: { className?: string }) {
  const dots = [3, 21, 39, 57, 75, 93];
  const rows = [3, 21, 39];

  return (
    <svg
      width="96"
      height="60"
      viewBox="0 0 96 60"
      aria-hidden="true"
      className={className}
      fill="currentColor"
      style={{ color: "var(--color-accent-border)" }}
    >
      {rows.flatMap((y) =>
        dots.map((x) => <circle key={`${x}-${y}`} cx={x} cy={y} r="1.6" />),
      )}
    </svg>
  );
}

function CurvedArrow({ className }: { className?: string }) {
  return (
    <svg
      width="70"
      height="100"
      viewBox="0 0 70 100"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.6"
      strokeLinecap="round"
      aria-hidden="true"
      className={`${className} text-text-2 rtl:-scale-x-100`}
    >
      <path d="M50 8C22 20 14 44 30 56c14 10 26-4 14-14-14-11-32 8-30 34" />
      <path d="M38 12l12-4-4 12" />
    </svg>
  );
}
