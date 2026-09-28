import "server-only";

import type {
  AboutContent,
  BlogContent,
  Category,
  ContactContent,
  Experience,
  Faq,
  HomeContent,
  NotFoundContent,
  PageContent,
  Paginated,
  Post,
  PostCard,
  ProcessStep,
  Project,
  ProjectCard,
  SearchResult,
  Service,
  ServiceSummary,
  Settings,
  Sitemap,
  Testimonial,
} from "@/types/api";

/**
 * The only door to the Laravel API. Server-side only: it sends the shared secret in
 * `X-Internal-Key`, tags every request so Laravel can revalidate exactly what changed, and
 * keeps a long safety-net revalidation (24h) — content updates arrive via on-demand revalidation.
 */
const SAFETY_NET_SECONDS = 60 * 60 * 24;

export class ApiError extends Error {
  constructor(
    message: string,
    readonly status: number,
  ) {
    super(message);
    this.name = "ApiError";
  }
}

type Options = { tags: string[]; dynamic?: boolean };

async function request<T>(
  path: string,
  { tags, dynamic = false }: Options,
): Promise<T | null> {
  const base = process.env.API_URL;
  const key = process.env.API_INTERNAL_KEY;

  if (!base || !key) {
    throw new ApiError("API_URL and API_INTERNAL_KEY must be set", 500);
  }

  const response = await fetch(`${base}${path}`, {
    headers: { Accept: "application/json", "X-Internal-Key": key },
    ...(dynamic
      ? { cache: "no-store" as const }
      : { next: { tags, revalidate: SAFETY_NET_SECONDS } }),
  });

  if (response.status === 404) return null;
  if (!response.ok)
    throw new ApiError(
      `API ${path} responded ${response.status}`,
      response.status,
    );

  return (await response.json()) as T;
}

/** Same as `request` but a missing resource is an error (for data every page needs). */
async function required<T>(path: string, options: Options): Promise<T> {
  const result = await request<T>(path, options);
  if (result === null) throw new ApiError(`API ${path} not found`, 404);
  return result;
}

const qs = (
  params: Record<string, string | number | boolean | undefined | null>,
) => {
  const search = new URLSearchParams();
  for (const [name, value] of Object.entries(params)) {
    if (value !== undefined && value !== null && value !== "")
      search.set(name, String(value));
  }
  const text = search.toString();
  return text ? `?${text}` : "";
};

export const getSettings = async () =>
  (await required<{ data: Settings }>("/settings", { tags: ["settings"] }))
    .data;

export async function getPage<C = Record<string, unknown>>(key: string) {
  const result = await request<{ data: PageContent<C> }>(`/pages/${key}`, {
    tags: ["pages", ...(key === "about" ? ["about"] : [])],
  });
  return result?.data ?? null;
}
export const getHomePage = () => getPage<HomeContent>("home");
export const getAboutPage = () => getPage<AboutContent>("about");
export const getContactPage = () => getPage<ContactContent>("contact");
export const getBlogPage = () => getPage<BlogContent>("blog");
export const getNotFoundPage = () => getPage<NotFoundContent>("not_found");

export const getPosts = (
  params: {
    page?: number;
    perPage?: number;
    category?: string;
    excludeFeatured?: boolean;
  } = {},
) =>
  required<Paginated<PostCard>>(
    `/posts${qs({ page: params.page, per_page: params.perPage, category: params.category, exclude_featured: params.excludeFeatured ? 1 : undefined })}`,
    {
      tags: [
        "posts",
        ...(params.category ? [`category:${params.category}`] : []),
      ],
    },
  );

export const getFeaturedPost = async (category?: string) =>
  (
    await required<{ data: PostCard | null }>(
      `/posts/featured${qs({ category })}`,
      { tags: ["posts", ...(category ? [`category:${category}`] : [])] },
    )
  ).data;

export const getPost = async (slug: string) =>
  (
    await request<{ data: Post }>(`/posts/${slug}`, {
      tags: ["posts", `post:${slug}`],
    })
  )?.data ?? null;

export const searchPosts = (params: {
  q: string;
  category?: string;
  sort?: string;
  page?: number;
}) =>
  required<SearchResult>(
    `/search${qs({ q: params.q, category: params.category, sort: params.sort, page: params.page })}`,
    { tags: ["search"], dynamic: true },
  );

export const getCategories = async () =>
  (
    await required<{ data: Category[] }>("/categories", {
      tags: ["categories"],
    })
  ).data;

export const getCategory = async (slug: string) =>
  (
    await request<{ data: Category }>(`/categories/${slug}`, {
      tags: ["categories", `category:${slug}`],
    })
  )?.data ?? null;

export const getProjects = async (featured = false) =>
  (
    await required<{ data: ProjectCard[] }>(
      `/projects${qs({ featured: featured ? 1 : undefined })}`,
      { tags: ["projects"] },
    )
  ).data;

export const getProject = async (slug: string) =>
  (
    await request<{ data: Project }>(`/projects/${slug}`, {
      tags: ["projects", `project:${slug}`],
    })
  )?.data ?? null;

export const getTestimonials = async (featured = false) =>
  (
    await required<{ data: Testimonial[] }>(
      `/testimonials${qs({ featured: featured ? 1 : undefined })}`,
      { tags: ["testimonials"] },
    )
  ).data;

export const getServices = async () =>
  (
    await required<{ data: ServiceSummary[] }>("/services", {
      tags: ["services"],
    })
  ).data;

export const getService = async (slug: string) =>
  (
    await request<{ data: Service }>(`/services/${slug}`, {
      tags: ["services", `service:${slug}`],
    })
  )?.data ?? null;

export const getExperiences = async () =>
  (await required<{ data: Experience[] }>("/experiences", { tags: ["about"] }))
    .data;

export const getProcessSteps = async () =>
  (
    await required<{ data: ProcessStep[] }>("/process-steps", {
      tags: ["about"],
    })
  ).data;

export const getFaqs = async (scope = "contact") =>
  (await required<{ data: Faq[] }>(`/faqs${qs({ scope })}`, { tags: ["faqs"] }))
    .data;

export const getSitemap = async () =>
  (await required<{ data: Sitemap }>("/sitemap", { tags: ["sitemap"] })).data;
