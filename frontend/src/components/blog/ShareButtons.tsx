"use client";

import { useState } from "react";
import { Check, Link2 } from "lucide-react";
import { LinkedInIcon, XIcon } from "@/components/ui/brand-icons";

const button =
  "flex size-10 items-center justify-center rounded-control border border-border-input text-text-2 transition-colors hover:border-accent hover:text-text";

export function ShareButtons({
  url,
  title,
  className,
}: {
  url: string;
  title: string;
  className?: string;
}) {
  const [copied, setCopied] = useState(false);

  const copy = async () => {
    try {
      await navigator.clipboard.writeText(url);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch {
      window.prompt("Copy this link", url);
    }
  };

  return (
    <div className={className}>
      <div className="tracking-eyebrow text-eyebrow mb-4 text-xs font-semibold uppercase">
        Share
      </div>
      <ul className="desktop:flex-col flex gap-3">
        <li>
          <a
            href={`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`}
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Share on LinkedIn"
            className={button}
          >
            <LinkedInIcon className="size-4.5" />
          </a>
        </li>
        <li>
          <a
            href={`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`}
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Share on X"
            className={button}
          >
            <XIcon className="size-4.5" />
          </a>
        </li>
        <li>
          <button
            type="button"
            onClick={copy}
            aria-label={copied ? "Link copied" : "Copy link"}
            className={button}
          >
            {copied ? (
              <Check className="text-success size-4.5" strokeWidth={1.8} />
            ) : (
              <Link2 className="size-4.5" strokeWidth={1.8} />
            )}
          </button>
          <span role="status" className="sr-only">
            {copied ? "Link copied to clipboard" : ""}
          </span>
        </li>
      </ul>
    </div>
  );
}
