/** Response types mirroring the Laravel API Resources (docs/02-architecture.md). */

export type IconName =
  | "alert"
  | "bell"
  | "book"
  | "bulb"
  | "calendar"
  | "card"
  | "cart"
  | "chat"
  | "check"
  | "clock"
  | "code"
  | "db"
  | "download"
  | "file"
  | "flow"
  | "grid"
  | "heart"
  | "home"
  | "mail"
  | "menu"
  | "minus"
  | "phone"
  | "pin"
  | "plan"
  | "plus"
  | "rocket"
  | "screen"
  | "search"
  | "send"
  | "server"
  | "shield"
  | "smile"
  | "trend"
  | "trophy"
  | "upload"
  | "user"
  | "x";

export type ApiImage = {
  url: string;
  card: string | null;
  md: string | null;
  lg: string | null;
  width: number | null;
  height: number | null;
  alt: string;
};

export type Seo = {
  meta_title: string | null;
  meta_description: string | null;
  canonical_url: string | null;
  noindex: boolean;
  og_image: ApiImage | null;
};

export type PaginationMeta = {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
};

export type Paginated<T> = { data: T[]; meta: PaginationMeta };

export type CategoryRef = { name: string; slug: string };

export type PostCard = {
  slug: string;
  title: string;
  excerpt: string;
  cover: ApiImage | null;
  category: CategoryRef;
  published_at: string;
  reading_time: number;
  featured: boolean;
};

export type TocItem = { id: string; text: string; level: number };

export type Post = PostCard & {
  body_html: string;
  toc: TocItem[];
  updated_at: string;
  tags: CategoryRef[];
  related_service: {
    slug: string;
    nav_label: string;
    lead: string;
    icon: IconName;
  } | null;
  author: {
    name: string;
    headline: string;
    bio: string | null;
    photo: ApiImage | null;
  };
  previous: { slug: string; title: string } | null;
  next: { slug: string; title: string } | null;
  related: PostCard[];
  seo: Seo;
};

export type Category = {
  name: string;
  slug: string;
  description: string;
  posts_count: number;
  seo: Seo;
};

export type SearchResult = {
  data: PostCard[];
  meta: PaginationMeta & { query: string; total_all: number };
  categories: { name: string; slug: string; count: number }[];
};

export type Testimonial = {
  quote: string;
  name: string;
  role: string | null;
  company: string | null;
  initials: string;
  rating: number;
  avatar: ApiImage | null;
};

export type ProjectCard = {
  slug: string;
  title: string;
  summary: string;
  cover: ApiImage | null;
  tags: string[];
  /** Every technology of the project (filter on /projects); `tags` is the first two. */
  stack: string[];
  featured: boolean;
};

export type Project = ProjectCard & {
  lead_html: string | null;
  client: string | null;
  role: string | null;
  timeline: string | null;
  year: number | null;
  live_url: string | null;
  challenge_html: string | null;
  solution_html: string | null;
  result_html: string | null;
  features: { icon: IconName; title: string; text: string }[];
  stack: string[];
  metrics: { value: string; label: string }[];
  screens: { image: ApiImage | null; caption: string | null }[];
  testimonial: Testimonial | null;
  service: { slug: string; nav_label: string; title: string } | null;
  next: ProjectCard | null;
  updated_at: string;
  seo: Seo;
};

export type ServiceSummary = {
  slug: string;
  nav_label: string;
  title: string;
  icon: IconName;
  lead: string;
};

export type Service = ServiceSummary & {
  h1: string;
  hero_image: ApiImage | null;
  floating_metric: { value: string; label: string } | null;
  pains: { icon: IconName; title: string; text: string }[];
  offers: { icon: IconName; title: string; text: string }[];
  why: { title: string | null; text_html: string | null; points: string[] };
  stack: string[];
  tiers: {
    name: string;
    price_from: string;
    subtitle: string | null;
    items: string[];
    highlighted: boolean;
  }[];
  faq: { question: string; answer: string }[];
  related_project:
    | (ProjectCard & {
        metrics: { value: string; label: string }[];
        testimonial: Testimonial | null;
      })
    | null;
  related_category: CategoryRef | null;
  posts: PostCard[];
  updated_at: string;
  seo: Seo;
};

export type Experience = {
  role: string;
  company: string;
  start_year: number;
  end_year: number | null;
  description: string | null;
};

export type ProcessStep = { icon: IconName; title: string; text: string };

export type Faq = { id: number; question: string; answer_html: string };

export type Settings = {
  brand: {
    name: string;
    tagline: string | null;
    logo: ApiImage | null;
    favicon: string | null;
    footer_text: string | null;
  };
  profile: {
    name: string;
    headline: string;
    bio_short: string | null;
    portrait: ApiImage | null;
    cv_url: string | null;
  };
  contact: {
    email: string;
    phone: string | null;
    whatsapp: string | null;
    city: string | null;
    working_hours: string | null;
    response_time: string | null;
  };
  socials: Partial<Record<"github" | "linkedin" | "x" | "instagram", string>>;
  stats: { icon: IconName; value: string; label: string }[];
  popular_searches: string[];
  contact_options: {
    needs: string[];
    budgets: string[];
    timelines: string[];
    upload: { types: string[]; max_mb: number; max_files: number };
  };
  tracking: {
    ga_measurement_id: string | null;
    gsc_verification: string | null;
    twitter_handle: string | null;
  };
  site_noindex: boolean;
  seo: {
    meta_title: string | null;
    meta_description: string | null;
    og_image: ApiImage | null;
  };
};

/** Structured copy of a fixed page. Fields holding rich text arrive as sanitized HTML strings. */
export type PageContent<C = Record<string, unknown>> = {
  key: string;
  title: string;
  content: C;
  body_html: string | null;
  toc: TocItem[];
  updated_at: string;
  seo: Seo;
};

export type HomeContent = {
  hero: {
    chip: string;
    greeting: string;
    lead: string;
    tech_label: string;
    technologies: string[];
    code_stack: string[];
    code_passion: string;
  };
  about_teaser: { eyebrow: string; heading: string; text: string };
  stack: {
    eyebrow: string;
    heading: string;
    text: string;
    groups: {
      icon: IconName;
      title: string;
      text: string;
      tags: string[];
      service_slug: string | null;
    }[];
  };
  projects: { eyebrow: string; heading: string };
  testimonials: { eyebrow: string; heading: string };
  blog: { eyebrow: string; heading: string };
  contact: { eyebrow: string; heading: string; text: string };
};

export type AboutContent = {
  hero: { chip: string; text: string };
  how: { eyebrow: string; heading: string; text: string };
  experience: { eyebrow: string; heading: string };
  toolbox: {
    eyebrow: string;
    heading: string;
    groups: { icon: IconName; title: string; tags: string[] }[];
  };
  cta: { heading: string; text: string; button: string };
  trust: { icon: IconName; value: string; label: string }[];
};

export type ContactContent = {
  hero: { eyebrow: string; text: string };
  faq: { eyebrow: string; heading: string; text: string };
};

export type BlogContent = { eyebrow: string; description: string };
export type ProjectsContent = BlogContent;
export type NotFoundContent = { text: string };

export type Sitemap = {
  posts_per_page: number;
  pages: { path: string; lastmod: string }[];
  posts: { slug: string; lastmod: string }[];
  categories: { slug: string; lastmod: string; posts_count: number }[];
  projects: { slug: string; lastmod: string }[];
  services: { slug: string; lastmod: string }[];
};
