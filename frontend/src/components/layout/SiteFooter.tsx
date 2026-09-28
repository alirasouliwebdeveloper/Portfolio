import Link from "next/link";
import { Container } from "@/components/ui/Container";
import { Icon } from "@/components/ui/icons";
import { Logo } from "@/components/ui/Logo";
import { SocialLinks } from "@/components/ui/SocialLinks";
import { getPosts, getServices, getSettings } from "@/lib/api";
import { mainNav } from "@/lib/nav";

const linkClass = "text-body text-muted transition-colors hover:text-text";
const clamp = "line-clamp-1";

function Column({
  title,
  children,
}: {
  title: string;
  children: React.ReactNode;
}) {
  return (
    <div className="flex flex-col gap-3.5">
      <h2 className="text-ui text-text mb-1 font-semibold">{title}</h2>
      {children}
    </div>
  );
}

export async function SiteFooter() {
  const [settings, services, latest] = await Promise.all([
    getSettings(),
    getServices(),
    getPosts({ perPage: 4 }),
  ]);
  const { brand, profile, contact, socials } = settings;

  const contactRows = [
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
    contact.working_hours && {
      icon: "clock" as const,
      label: contact.working_hours,
    },
  ].filter(Boolean) as {
    icon: "mail" | "phone" | "pin" | "clock";
    label: string;
    href?: string;
  }[];

  return (
    <footer className="border-line bg-bg-footer tablet:pt-16 desktop:pt-20 border-t pt-14 pb-8">
      <Container>
        <div className="tablet:grid-cols-2 tablet:gap-x-14 tablet:gap-y-12 desktop:grid-cols-[1.4fr_1fr_1.1fr_1.3fr_1.3fr] grid gap-10">
          <div className="tablet:col-span-2 desktop:col-span-1 flex max-w-85 flex-col gap-4.5">
            <Logo brand={brand} />
            {profile.bio_short ? (
              <p className="text-body text-muted">{profile.bio_short}</p>
            ) : null}
            <SocialLinks socials={socials} />
          </div>

          <Column title="Pages">
            {mainNav.map((item) => (
              <Link key={item.label} href={item.href} className={linkClass}>
                {item.label}
              </Link>
            ))}
          </Column>

          <Column title="Services">
            {services.map((service) => (
              <Link
                key={service.slug}
                href={`/services/${service.slug}`}
                className={linkClass}
              >
                {service.nav_label}
              </Link>
            ))}
          </Column>

          <Column title="Latest articles">
            {latest.data.map((post) => (
              <Link
                key={post.slug}
                href={`/blog/${post.slug}`}
                className={`${linkClass} ${clamp}`}
              >
                {post.title}
              </Link>
            ))}
          </Column>

          <Column title="Get in touch">
            {contactRows.map((row) => {
              const content = (
                <>
                  <Icon
                    name={row.icon}
                    className="text-icon-soft size-4 shrink-0"
                  />
                  <span>{row.label}</span>
                </>
              );
              return row.href ? (
                <a
                  key={row.label}
                  href={row.href}
                  className={`${linkClass} flex items-center gap-2.5`}
                >
                  {content}
                </a>
              ) : (
                <div
                  key={row.label}
                  className={`${linkClass} flex items-center gap-2.5`}
                >
                  {content}
                </div>
              );
            })}
          </Column>
        </div>

        <div className="border-line text-meta text-dim tablet:flex-row tablet:items-center tablet:justify-between mt-12 flex flex-col gap-4 border-t pt-6">
          <p>
            © {new Date().getFullYear()}{" "}
            {brand.footer_text || `${brand.name}. All rights reserved.`}
          </p>
          <ul className="flex flex-wrap gap-x-6 gap-y-2">
            <li>
              <Link href="/privacy" className="hover:text-text">
                Privacy Policy
              </Link>
            </li>
            <li>
              <Link href="/terms" className="hover:text-text">
                Terms of Service
              </Link>
            </li>
            <li>
              <Link href="/sitemap.xml" className="hover:text-text">
                Sitemap
              </Link>
            </li>
          </ul>
        </div>
      </Container>
    </footer>
  );
}
