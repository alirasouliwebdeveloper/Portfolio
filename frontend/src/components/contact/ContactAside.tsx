import { Card } from "@/components/ui/Card";
import { IconTile } from "@/components/ui/IconTile";
import { SocialLinks } from "@/components/ui/SocialLinks";
import type { IconName } from "@/components/ui/icons";
import type { Settings } from "@/types/api";

export function ContactAside({ settings }: { settings: Settings }) {
  const { contact, socials } = settings;
  const rows = [
    {
      icon: "mail",
      label: "Email",
      value: contact.email,
      href: `mailto:${contact.email}`,
    },
    contact.phone && {
      icon: "phone",
      label: "Phone / WhatsApp",
      value: contact.phone,
      href: `tel:${contact.phone.replace(/\s/g, "")}`,
    },
    contact.city && { icon: "pin", label: "Based in", value: contact.city },
    contact.response_time && {
      icon: "clock",
      label: "Response time",
      value: contact.response_time,
    },
  ].filter(Boolean) as {
    icon: IconName;
    label: string;
    value: string;
    href?: string;
  }[];

  return (
    <aside aria-label="Contact details" className="flex flex-col gap-3.5">
      {rows.map((row) => (
        <Card key={row.label} className="flex items-center gap-4 p-5">
          <IconTile name={row.icon} size="sm" tone="chip" />
          <div className="min-w-0">
            <div className="text-caption text-dim">{row.label}</div>
            {row.href ? (
              <a
                href={row.href}
                className="text-ui text-text hover:text-link block truncate font-semibold"
              >
                {row.value}
              </a>
            ) : (
              <div className="text-ui text-text font-semibold">{row.value}</div>
            )}
          </div>
        </Card>
      ))}
      <Card className="flex flex-col gap-3.5 p-5">
        <div className="text-caption text-dim">Find me online</div>
        <SocialLinks socials={socials} />
      </Card>
    </aside>
  );
}
