import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { Suspense } from "react";
import { ContactAside } from "@/components/contact/ContactAside";
import { ContactForm } from "@/components/contact/ContactForm";
import { Accordion } from "@/components/ui/Accordion";
import { Container } from "@/components/ui/Container";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { JsonLd } from "@/components/ui/JsonLd";
import { RichContent } from "@/components/ui/RichContent";
import { Section } from "@/components/ui/Section";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { getContactPage, getFaqs, getSettings } from "@/lib/api";
import { contactPageJsonLd, faqJsonLd } from "@/lib/jsonld";
import { pageMetadata } from "@/lib/seo";

export async function generateMetadata(): Promise<Metadata> {
  const [page, settings] = await Promise.all([getContactPage(), getSettings()]);
  return page ? pageMetadata({ page, settings, path: "/contact" }) : {};
}

export default async function ContactPage() {
  const [page, settings, faqs] = await Promise.all([
    getContactPage(),
    getSettings(),
    getFaqs("contact"),
  ]);
  if (!page) notFound();
  const { hero, faq } = page.content;

  return (
    <>
      <JsonLd
        data={[
          contactPageJsonLd(page),
          ...(faqs.length
            ? [
                faqJsonLd(
                  faqs.map((item) => ({
                    question: item.question,
                    answer: item.answer_html
                      .replace(/<[^>]+>/g, " ")
                      .replace(/\s+/g, " ")
                      .trim(),
                  })),
                ),
              ]
            : []),
        ]}
      />

      <section
        aria-labelledby="contact-title"
        className="tablet:pt-14 desktop:pt-20 bg-bg pt-10 pb-8"
      >
        <Container>
          <Eyebrow>{hero.eyebrow}</Eyebrow>
          <h1
            id="contact-title"
            className="text-page-h1 tracking-h1 text-text mt-3"
          >
            {page.title}
          </h1>
          <p className="text-subhead text-muted mt-4 max-w-150">{hero.text}</p>
        </Container>
      </section>

      <Section flush className="tablet:pb-16 desktop:pb-24 pt-4 pb-12">
        <div className="desktop:grid-cols-[minmax(0,1fr)_28.75rem] grid items-start gap-6">
          <Suspense fallback={<div className="min-h-160" aria-hidden="true" />}>
            <ContactForm options={settings.contact_options} />
          </Suspense>
          <ContactAside settings={settings} />
        </div>
      </Section>

      {faqs.length > 0 ? (
        <Section variant="alt" aria-labelledby="faq-title">
          <SectionHeading
            id="faq-title"
            eyebrow={faq.eyebrow}
            title={faq.heading}
            description={faq.text}
          />
          <Accordion
            className="mx-auto mt-12 max-w-190"
            items={faqs.map((item) => ({
              id: String(item.id),
              question: item.question,
              answer: <RichContent html={item.answer_html} />,
            }))}
          />
        </Section>
      ) : null}
    </>
  );
}
