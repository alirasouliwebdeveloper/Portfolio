import type { SVGProps } from "react";

/*
 * Brand marks keep their own official colors — they are logos, not UI, so they are the one
 * place raw hex values are allowed (docs/03-design-system.md tokens apply to interface colors).
 */
type Props = SVGProps<SVGSVGElement>;

export function HtmlIcon(props: Props) {
  return (
    <svg viewBox="0 0 36 36" aria-hidden="true" {...props}>
      <path d="M6 3h24l-2.2 25L18 33l-9.8-5z" fill="#E44D26" />
      <path d="M18 5.5v25l7.8-3.2 1.9-21.8z" fill="#F16529" />
      <text
        x="18"
        y="23"
        textAnchor="middle"
        fontSize="13"
        fontWeight="700"
        fill="#FFFFFF"
      >
        5
      </text>
    </svg>
  );
}

export function JavaScriptIcon(props: Props) {
  return (
    <svg viewBox="0 0 34 34" aria-hidden="true" {...props}>
      <rect width="34" height="34" rx="3" fill="#F7DF1E" />
      <text
        x="30"
        y="29"
        textAnchor="end"
        fontSize="14"
        fontWeight="700"
        fill="#1A1A1A"
      >
        JS
      </text>
    </svg>
  );
}

export function TypeScriptIcon(props: Props) {
  return (
    <svg viewBox="0 0 34 34" aria-hidden="true" {...props}>
      <rect width="34" height="34" rx="3" fill="#3178C6" />
      <text
        x="30"
        y="29"
        textAnchor="end"
        fontSize="14"
        fontWeight="700"
        fill="#FFFFFF"
      >
        TS
      </text>
    </svg>
  );
}

export function ReactIcon(props: Props) {
  return (
    <svg
      viewBox="0 0 36 36"
      fill="none"
      stroke="#61DAFB"
      strokeWidth="1.6"
      aria-hidden="true"
      {...props}
    >
      <ellipse cx="18" cy="18" rx="15" ry="6" />
      <ellipse cx="18" cy="18" rx="15" ry="6" transform="rotate(60 18 18)" />
      <ellipse cx="18" cy="18" rx="15" ry="6" transform="rotate(120 18 18)" />
      <circle cx="18" cy="18" r="2.6" fill="#61DAFB" stroke="none" />
    </svg>
  );
}

export function NodeIcon(props: Props) {
  return (
    <svg viewBox="0 0 34 36" aria-hidden="true" {...props}>
      <path
        d="M17 2l14 8v16l-14 8-14-8V10z"
        fill="none"
        stroke="#68A063"
        strokeWidth="2.2"
      />
      <text
        x="17"
        y="23"
        textAnchor="middle"
        fontSize="12"
        fontWeight="700"
        fill="#68A063"
      >
        n
      </text>
    </svg>
  );
}

export function GitIcon(props: Props) {
  return (
    <svg viewBox="0 0 34 34" aria-hidden="true" {...props}>
      <rect
        x="5"
        y="5"
        width="24"
        height="24"
        rx="3"
        transform="rotate(45 17 17)"
        fill="#F05032"
      />
      <path
        d="M13 11l8 8M17 15v8"
        stroke="#FFFFFF"
        strokeWidth="2"
        strokeLinecap="round"
      />
    </svg>
  );
}

/** A neutral mark for technologies without a dedicated logo. */
export function GenericTechIcon({
  label,
  ...props
}: Props & { label: string }) {
  return (
    <svg viewBox="0 0 34 34" aria-hidden="true" {...props}>
      <rect width="34" height="34" rx="6" fill="currentColor" opacity="0.18" />
      <text
        x="17"
        y="22"
        textAnchor="middle"
        fontSize="12"
        fontWeight="700"
        fill="currentColor"
      >
        {label.slice(0, 2)}
      </text>
    </svg>
  );
}

const known: Record<string, (props: Props) => React.JSX.Element> = {
  html: HtmlIcon,
  javascript: JavaScriptIcon,
  typescript: TypeScriptIcon,
  react: ReactIcon,
  "node.js": NodeIcon,
  node: NodeIcon,
  git: GitIcon,
};

export function TechIcon({
  name,
  className,
}: {
  name: string;
  className?: string;
}) {
  const Icon = known[name.toLowerCase()];
  return (
    <span title={name} className="flex">
      {Icon ? (
        <Icon className={className} />
      ) : (
        <GenericTechIcon label={name} className={className} />
      )}
    </span>
  );
}

/* Social networks (lucide dropped brand icons). Monochrome: they inherit the text color. */
export function GitHubIcon(props: Props) {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" {...props}>
      <path d="M12 2a10 10 0 0 0-3.16 19.49c.5.09.68-.22.68-.48v-1.7c-2.78.6-3.37-1.34-3.37-1.34-.45-1.16-1.11-1.47-1.11-1.47-.91-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.9 1.52 2.34 1.08 2.91.83.09-.65.35-1.09.63-1.34-2.22-.25-4.55-1.11-4.55-4.94 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.64 0 0 .84-.27 2.75 1.02a9.5 9.5 0 0 1 5 0c1.91-1.29 2.75-1.02 2.75-1.02.55 1.37.2 2.39.1 2.64.64.7 1.03 1.59 1.03 2.68 0 3.84-2.34 4.68-4.57 4.93.36.31.68.92.68 1.85v2.74c0 .27.18.58.69.48A10 10 0 0 0 12 2Z" />
    </svg>
  );
}

export function LinkedInIcon(props: Props) {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" {...props}>
      <path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM3 9.75h4v11.5H3V9.75Zm6.5 0h3.83v1.57h.06c.53-1 1.84-2.07 3.79-2.07 4.05 0 4.8 2.66 4.8 6.12v5.88h-4v-5.2c0-1.24-.02-2.83-1.73-2.83-1.73 0-2 1.35-2 2.74v5.29h-4V9.75Z" />
    </svg>
  );
}

export function XIcon(props: Props) {
  return (
    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" {...props}>
      <path d="M17.75 3h3.07l-6.7 7.66L22 21h-6.17l-4.84-6.33L5.45 21H2.38l7.17-8.19L2 3h6.33l4.37 5.78L17.75 3Zm-1.08 16.16h1.7L7.4 4.75H5.58l11.09 14.41Z" />
    </svg>
  );
}

export function InstagramIcon(props: Props) {
  return (
    <svg
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.8"
      aria-hidden="true"
      {...props}
    >
      <rect x="3" y="3" width="18" height="18" rx="5" />
      <circle cx="12" cy="12" r="4" />
      <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
    </svg>
  );
}
