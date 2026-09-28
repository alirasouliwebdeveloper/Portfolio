import { TeaserForm } from "@/components/contact/TeaserForm";
import { Eyebrow } from "@/components/ui/Eyebrow";
import { IconTile } from "@/components/ui/IconTile";
import { Section } from "@/components/ui/Section";
import type { HomeContent, Settings } from "@/types/api";

export function ContactTeaser({
  content,
  settings,
}: {
  content: HomeContent["contact"];
  settings: Settings;
}) {
  const { contact, contact_options } = settings;
  const rows = [
    contact.email && {
      icon: "mail" as const,
      label: contact.email,
      href: `mailto:${contact.email}`,
    },
    contact.phone && {
      icon: "phone" as const,
      label: contact.phone,
      href: `tel:${contact.phone.replace(/\s/g, "")}`,
    },
    contact.city && { icon: "pin" as const, label: contact.city },
  ].filter(Boolean) as {
    icon: "mail" | "phone" | "pin";
    label: string;
    href?: string;
  }[];

  return (
    <Section variant="alt" id="contact" aria-labelledby="contact-teaser-title">
      <div className="desktop:grid-cols-2 desktop:items-center desktop:gap-24 grid gap-12">
        <div>
          <Eyebrow>{content.eyebrow}</Eyebrow>
          <h2
            id="contact-teaser-title"
            className="text-h2 tracking-h2 text-text mt-2.5"
          >
            {content.heading}
          </h2>
          <p className="text-subhead text-muted mt-4 max-w-120">
            {content.text}
          </p>
          <ul className="mt-8 flex flex-col gap-4">
            {rows.map((row) => (
              <li key={row.label} className="flex items-center gap-4">
                <IconTile name={row.icon} size="sm" />
                {row.href ? (
                  <a
                    href={row.href}
                    className="text-body text-text-2 hover:text-text"
                  >
                    {row.label}
                  </a>
                ) : (
                  <span className="text-body text-text-2">{row.label}</span>
                )}
              </li>
            ))}
          </ul>
        </div>
        <TeaserForm needs={contact_options.needs} />
      </div>
    </Section>
  );
}
