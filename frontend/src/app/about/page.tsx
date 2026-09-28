import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { CtaBand } from "@/components/sections/CtaBand";
import { ProcessSteps } from "@/components/sections/ProcessSteps";
import { Timeline } from "@/components/sections/Timeline";
import { ToolboxGroups } from "@/components/sections/ToolboxGroups";
import { Button } from "@/components/ui/Button";
import { Container } from "@/components/ui/Container";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { ArrowUpRight } from "@/components/ui/icons";
import { Media } from "@/components/ui/Media";
import { RichContent } from "@/components/ui/RichContent";
import { Section } from "@/components/ui/Section";
import { SectionHeading } from "@/components/ui/SectionHeading";
import {
  getAboutPage,
  getExperiences,
  getProcessSteps,
  getSettings,
} from "@/lib/api";
import { personJsonLd, profilePageJsonLd } from "@/lib/jsonld";
import { pageMetadata } from "@/lib/seo";
import { JsonLd } from "@/components/ui/JsonLd";
import { Download } from "lucide-react";

export async function generateMetadata(): Promise<Metadata> {
  const [page, settings] = await Promise.all([getAboutPage(), getSettings()]);
  return page ? pageMetadata({ page, settings, path: "/about" }) : {};
}

export default async function AboutPage() {
  const [page, settings, experiences, steps] = await Promise.all([
    getAboutPage(),
    getSettings(),
    getExperiences(),
    getProcessSteps(),
  ]);
  if (!page) notFound();

  const { content } = page;
  const { profile } = settings;

  return (
    <>
      <JsonLd
        data={[personJsonLd(settings), profilePageJsonLd(page, settings)]}
      />

      <section
        aria-labelledby="about-title"
        className="border-line-soft bg-bg tablet:pt-14 tablet:pb-16 desktop:py-24 border-b pt-10 pb-12"
      >
        <Container className="desktop:grid-cols-[minmax(0,40rem)_1fr] desktop:gap-24 grid items-center gap-12">
          <div className="flex flex-col items-start">
            <Eyebrow variant="chip">{content.hero.chip}</Eyebrow>
            <h1
              id="about-title"
              className="text-page-h1 tracking-h1 text-text mt-6 leading-[1.15]"
            >
              {page.title}
            </h1>
            <RichContent
              html={content.hero.text}
              variant="body"
              className="text-lead mt-6 [&>p]:mt-4 [&>p:first-child]:mt-0"
            />
            <div className="tablet:w-auto tablet:flex-row mt-8 flex w-full flex-col gap-4">
              <Button href="/contact" fullWidth className="tablet:w-auto">
                Get In Touch <ArrowUpRight className="size-3.5" />
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
          </div>

          <div className="desktop:justify-end flex justify-center">
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
          </div>
        </Container>
      </section>

      <Section variant="alt" aria-labelledby="how-title">
        <SectionHeading
          eyebrow={content.how.eyebrow}
          title={content.how.heading}
          description={content.how.text}
          id="how-title"
        />
        <div className="mt-12">
          <ProcessSteps steps={steps} />
        </div>
      </Section>

      <Section aria-labelledby="experience-title">
        <SectionHeading
          eyebrow={content.experience.eyebrow}
          title={content.experience.heading}
          id="experience-title"
        />
        <div className="mt-12">
          <Timeline items={experiences} />
        </div>
      </Section>

      <Section variant="alt" aria-labelledby="toolbox-title">
        <SectionHeading
          eyebrow={content.toolbox.eyebrow}
          title={content.toolbox.heading}
          id="toolbox-title"
        />
        <div className="mt-12">
          <ToolboxGroups groups={content.toolbox.groups} />
        </div>
      </Section>

      <CtaBand heading={content.cta.heading} text={content.cta.text}>
        <Button href="/contact">
          {content.cta.button} <ArrowUpRight className="size-3.5" />
        </Button>
      </CtaBand>
    </>
  );
}
