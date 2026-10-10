import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { ServiceHero } from "@/components/service/ServiceHero";
import {
  ArticlesSection,
  FaqSection,
  OffersSection,
  OtherServices,
  PainSection,
  PricingSection,
  StackSection,
  WorkSection,
} from "@/components/service/ServiceSections";
import { CtaBand } from "@/components/sections/CtaBand";
import { ProcessSteps } from "@/components/sections/ProcessSteps";
import { Button } from "@/components/ui/Button";
import { JsonLd } from "@/components/ui/JsonLd";
import { Section } from "@/components/ui/Section";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { ArrowUpRight, Icon } from "@/components/ui/icons";
import {
  getAboutPage,
  getProcessSteps,
  getService,
  getServices,
  getSettings,
  getSitemap,
} from "@/lib/api";
import { breadcrumbJsonLd, faqJsonLd, serviceJsonLd } from "@/lib/jsonld";
import { buildMetadata } from "@/lib/seo";
import { redirectIfMoved } from "@/lib/redirects";

export const dynamicParams = true;

export async function generateStaticParams() {
  const sitemap = await getSitemap();
  return sitemap.services.map((service) => ({ slug: service.slug }));
}

export async function generateMetadata({
  params,
}: PageProps<"/services/[slug]">): Promise<Metadata> {
  const { slug } = await params;
  const [service, settings] = await Promise.all([
    getService(slug),
    getSettings(),
  ]);
  if (!service) return {};

  return buildMetadata({
    title: service.title,
    seo: service.seo,
    settings,
    path: `/services/${service.slug}`,
    fallbackImage: service.hero_image,
  });
}

export default async function ServicePage({
  params,
}: PageProps<"/services/[slug]">) {
  const { slug } = await params;
  const [service, settings, steps, services, about] = await Promise.all([
    getService(slug),
    getSettings(),
    getProcessSteps(),
    getServices(),
    getAboutPage(),
  ]);
  if (!service) {
    await redirectIfMoved(`/services/${slug}`);
    notFound();
  }

  return (
    <>
      <JsonLd
        data={[
          serviceJsonLd(service, settings),
          breadcrumbJsonLd([
            { name: "Home", path: "/" },
            { name: service.nav_label, path: `/services/${service.slug}` },
          ]),
          ...(service.faq.length > 0 ? [faqJsonLd(service.faq)] : []),
        ]}
      />
      <ServiceHero service={service} trust={about?.content.trust ?? []} />
      <PainSection service={service} />
      <OffersSection service={service} />
      <StackSection service={service} />
      <Section aria-labelledby="how-it-works-title">
        <SectionHeading
          eyebrow="How it works"
          title="From first call to launch"
          id="how-it-works-title"
        />
        <div className="mt-12">
          <ProcessSteps steps={steps} />
        </div>
      </Section>
      <WorkSection service={service} />
      <PricingSection service={service} />
      <FaqSection service={service} />
      <ArticlesSection service={service} />
      <OtherServices
        services={services.filter((item) => item.slug !== service.slug)}
      />
      <CtaBand
        heading="Ready to start your project?"
        text="Tell me what you need — you'll get a clear quote within two working days."
      >
        <Button href={`/contact?service=${service.slug}`}>
          Get a Free Quote <ArrowUpRight className="size-3.5" />
        </Button>
        {settings.contact.email ? (
          <Button href={`mailto:${settings.contact.email}`} variant="outline">
            <Icon name="mail" className="size-4" /> Email Me
          </Button>
        ) : null}
      </CtaBand>
    </>
  );
}
