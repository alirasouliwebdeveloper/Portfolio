import type { Metadata } from "next";
import { AboutTeaser } from "@/components/sections/AboutTeaser";
import { ContactTeaser } from "@/components/sections/ContactTeaser";
import { FeaturedProjects } from "@/components/sections/FeaturedProjects";
import { Hero } from "@/components/sections/Hero";
import { LatestArticles } from "@/components/sections/LatestArticles";
import { StackSection } from "@/components/sections/StackSection";
import { TestimonialsSection } from "@/components/sections/TestimonialsSection";
import {
  getHomePage,
  getPosts,
  getProjects,
  getServices,
  getSettings,
  getTestimonials,
} from "@/lib/api";
import { JsonLd } from "@/components/ui/JsonLd";
import { personJsonLd, websiteJsonLd } from "@/lib/jsonld";
import { pageMetadata } from "@/lib/seo";
import { notFound } from "next/navigation";

export async function generateMetadata(): Promise<Metadata> {
  const [page, settings] = await Promise.all([getHomePage(), getSettings()]);
  if (!page) return {};

  return pageMetadata({ page, settings, path: "/", absoluteTitle: true });
}

export default async function HomePage() {
  const [page, settings, projects, testimonials, posts, services] =
    await Promise.all([
      getHomePage(),
      getSettings(),
      getProjects(true),
      getTestimonials(true),
      getPosts({ perPage: 3 }),
      getServices(),
    ]);
  if (!page) notFound();

  const { content } = page;

  return (
    <>
      <JsonLd data={[personJsonLd(settings), websiteJsonLd(settings)]} />
      <Hero
        content={content.hero}
        title={page.title}
        settings={settings}
        firstProject={projects[0] ?? null}
      />
      <AboutTeaser content={content.about_teaser} stats={settings.stats} />
      <StackSection content={content.stack} services={services} />
      <FeaturedProjects content={content.projects} projects={projects} />
      <TestimonialsSection
        content={content.testimonials}
        testimonials={testimonials}
      />
      <LatestArticles content={content.blog} posts={posts.data} />
      <ContactTeaser content={content.contact} settings={settings} />
    </>
  );
}
