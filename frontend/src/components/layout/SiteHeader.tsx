import { Button } from "@/components/ui/Button";
import { ArrowUpRight } from "@/components/ui/icons";
import { Logo } from "@/components/ui/Logo";
import { SocialLinks } from "@/components/ui/SocialLinks";
import { Container } from "@/components/ui/Container";
import { getSettings } from "@/lib/api";
import { MobileMenu } from "./MobileMenu";
import { NavLinks } from "./NavLinks";

export async function SiteHeader() {
  const { brand, socials } = await getSettings();

  return (
    <header className="border-line bg-bg border-b">
      <Container className="h-header flex items-center justify-between">
        <Logo brand={brand} />

        <NavLinks className="desktop:block hidden" />

        <div className="flex items-center gap-3">
          <Button href="/contact" size="nav" className="max-tablet:hidden">
            Hire Me <ArrowUpRight className="max-desktop:hidden size-3.5" />
          </Button>
          <MobileMenu
            logo={<Logo brand={brand} />}
            socials={<SocialLinks socials={socials} />}
          />
        </div>
      </Container>
    </header>
  );
}
