import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { TextPage } from "@/components/sections/TextPage";
import { getPage, getSettings } from "@/lib/api";
import { pageMetadata } from "@/lib/seo";

export async function generateMetadata(): Promise<Metadata> {
  const [page, settings] = await Promise.all([
    getPage("privacy"),
    getSettings(),
  ]);
  return page ? pageMetadata({ page, settings, path: "/privacy" }) : {};
}

export default async function Page() {
  const page = await getPage("privacy");
  if (!page) notFound();

  return <TextPage page={page} path="/privacy" />;
}
