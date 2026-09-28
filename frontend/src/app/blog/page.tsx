import { BlogListPage, blogListMetadata } from "@/components/blog/BlogListPage";

export const generateMetadata = () => blogListMetadata({}, 1);

export default function BlogPage() {
  return <BlogListPage />;
}
