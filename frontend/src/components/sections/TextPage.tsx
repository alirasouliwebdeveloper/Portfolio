import { Breadcrumbs } from "@/components/ui/Breadcrumbs";
import { RichContent } from "@/components/ui/RichContent";
import { Section } from "@/components/ui/Section";
import type { PageContent } from "@/types/api";

/** Simple text pages (privacy, terms): reuses the article typography. */
export function TextPage({
  page,
}: {
  page: PageContent<unknown>;
  path?: string;
}) {
  return (
    <Section aria-labelledby="text-page-title">
      <Breadcrumbs
        items={[{ label: "Home", href: "/" }, { label: page.title }]}
      />
      <h1
        id="text-page-title"
        className="text-page-h1 tracking-h1 text-text mt-8 leading-[1.15]"
      >
        {page.title}
      </h1>
      <RichContent
        html={page.body_html ?? ""}
        variant="article"
        className="text-text-2 mt-8 max-w-200"
      />
    </Section>
  );
}
