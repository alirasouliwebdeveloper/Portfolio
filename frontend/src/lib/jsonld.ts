import type {
  PageContent,
  Post,
  Project,
  Service,
  Settings,
} from "@/types/api";
import { absoluteUrl, siteUrl } from "./seo";

const context = "https://schema.org";

const imageUrl = (
  image:
    { lg: string | null; md: string | null; url: string } | null | undefined,
) => image?.lg ?? image?.md ?? image?.url;

export const personId = () => `${siteUrl()}/#person`;

export function personJsonLd(settings: Settings) {
  return {
    "@context": context,
    "@type": "Person",
    "@id": personId(),
    name: settings.profile.name,
    jobTitle: settings.profile.headline,
    description: settings.profile.bio_short ?? undefined,
    url: siteUrl(),
    image: imageUrl(settings.profile.portrait),
    email: settings.contact.email
      ? `mailto:${settings.contact.email}`
      : undefined,
    telephone: settings.contact.phone ?? undefined,
    address: settings.contact.city
      ? { "@type": "PostalAddress", addressLocality: settings.contact.city }
      : undefined,
    sameAs: Object.values(settings.socials).filter((url) => url && url !== "#"),
  };
}

export const websiteJsonLd = (settings: Settings) => ({
  "@context": context,
  "@type": "WebSite",
  "@id": `${siteUrl()}/#website`,
  url: siteUrl(),
  name: settings.brand.name,
  publisher: { "@id": personId() },
  potentialAction: {
    "@type": "SearchAction",
    target: {
      "@type": "EntryPoint",
      urlTemplate: `${siteUrl()}/blog/search?q={search_term_string}`,
    },
    "query-input": "required name=search_term_string",
  },
});

export const profilePageJsonLd = (
  page: PageContent<unknown>,
  settings: Settings,
) => ({
  "@context": context,
  "@type": "ProfilePage",
  url: absoluteUrl("/about"),
  name: page.seo.meta_title ?? page.title,
  dateModified: page.updated_at,
  mainEntity: { "@id": personId() },
  isPartOf: { "@id": `${siteUrl()}/#website` },
  about: { "@type": "Person", name: settings.profile.name },
});

export const contactPageJsonLd = (page: PageContent<unknown>) => ({
  "@context": context,
  "@type": "ContactPage",
  url: absoluteUrl("/contact"),
  name: page.seo.meta_title ?? page.title,
  about: { "@id": personId() },
});

export const breadcrumbJsonLd = (items: { name: string; path: string }[]) => ({
  "@context": context,
  "@type": "BreadcrumbList",
  itemListElement: items.map((item, index) => ({
    "@type": "ListItem",
    position: index + 1,
    name: item.name,
    item: absoluteUrl(item.path),
  })),
});

export const blogPostingJsonLd = (post: Post, settings: Settings) => ({
  "@context": context,
  "@type": "BlogPosting",
  headline: post.title,
  description: post.seo.meta_description ?? post.excerpt,
  image: imageUrl(post.seo.og_image ?? post.cover),
  datePublished: post.published_at,
  dateModified: post.updated_at,
  mainEntityOfPage: absoluteUrl(`/blog/${post.slug}`),
  author: {
    "@type": "Person",
    name: post.author.name,
    url: siteUrl(),
    "@id": personId(),
  },
  publisher: {
    "@type": "Person",
    name: settings.profile.name,
    "@id": personId(),
  },
  articleSection: post.category.name,
  keywords: post.tags.map((tag) => tag.name).join(", ") || undefined,
});

export const creativeWorkJsonLd = (project: Project) => ({
  "@context": context,
  "@type": "CreativeWork",
  name: project.title,
  description: project.seo.meta_description ?? project.summary,
  image: imageUrl(project.seo.og_image ?? project.cover),
  url: absoluteUrl(`/projects/${project.slug}`),
  dateModified: project.updated_at,
  author: { "@id": personId() },
  creator: { "@id": personId() },
  keywords: project.stack.join(", ") || undefined,
});

export const serviceJsonLd = (service: Service, settings: Settings) => ({
  "@context": context,
  "@type": "Service",
  name: service.title,
  description: service.seo.meta_description ?? service.lead,
  url: absoluteUrl(`/services/${service.slug}`),
  provider: { "@id": personId() },
  areaServed: settings.contact.city ?? undefined,
  serviceType: service.nav_label,
});

export const faqJsonLd = (items: { question: string; answer: string }[]) => ({
  "@context": context,
  "@type": "FAQPage",
  mainEntity: items.map((item) => ({
    "@type": "Question",
    name: item.question,
    acceptedAnswer: { "@type": "Answer", text: item.answer },
  })),
});
