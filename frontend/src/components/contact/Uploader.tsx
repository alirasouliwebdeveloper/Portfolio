"use client";

import {
  useCallback,
  useEffect,
  useId,
  useRef,
  useState,
  type DragEvent,
} from "react";
import { Check, File as FileIcon, Upload, X } from "lucide-react";
import { Icon } from "@/components/ui/icons";
import { cn } from "@/lib/cn";
import type { Settings } from "@/types/api";

type Rules = Settings["contact_options"]["upload"];
type Item = {
  id: string;
  name: string;
  size: number;
  status: "uploading" | "done" | "error";
  progress: number;
  uuid?: string;
  error?: string;
};

const MB = 1024 * 1024;
const formatSize = (bytes: number) =>
  bytes >= MB
    ? `${(bytes / MB).toFixed(bytes >= 10 * MB ? 0 : 1)} MB`
    : `${Math.max(1, Math.round(bytes / 1024))} KB`;

/** Same rules as Laravel (which validates again): the browser check is only for instant feedback. */
function validate(file: File, rules: Rules): string | null {
  const extension = file.name.split(".").pop()?.toLowerCase() ?? "";
  if (!rules.types.includes(extension))
    return "This file type isn’t supported.";
  if (file.size > rules.max_mb * MB)
    return `${Math.round(file.size / MB)} MB — files must be ${rules.max_mb} MB or smaller.`;
  return null;
}

type UploaderProps = {
  rules: Rules;
  /** Called with the number of uploads still running, so the form can block submitting. */
  onBusyChange?: (busy: boolean) => void;
};

/** Dropzone + file rows with the four designed states. Uploads go straight to Laravel to get real progress. */
export function Uploader({ rules, onBusyChange }: UploaderProps) {
  const [session, setSession] = useState<string>("");
  const [items, setItems] = useState<Item[]>([]);
  const [dragging, setDragging] = useState(false);
  const requests = useRef(new Map<string, XMLHttpRequest>());
  const input = useRef<HTMLInputElement>(null);
  const hintId = useId();
  const uploadUrl = process.env.NEXT_PUBLIC_UPLOAD_URL ?? "";

  useEffect(() => {
    const running = requests.current;
    return () => running.forEach((request) => request.abort());
  }, []);

  const busy = items.some((item) => item.status === "uploading");
  useEffect(() => onBusyChange?.(busy), [busy, onBusyChange]);

  const patch = useCallback((id: string, changes: Partial<Item>) => {
    setItems((current) =>
      current.map((item) => (item.id === id ? { ...item, ...changes } : item)),
    );
  }, []);

  const send = useCallback(
    (id: string, file: File, uploadSession: string) => {
      const request = new XMLHttpRequest();
      requests.current.set(id, request);
      request.open("POST", uploadUrl);
      request.setRequestHeader("Accept", "application/json");
      request.upload.onprogress = (event) => {
        if (event.lengthComputable)
          patch(id, {
            progress: Math.round((event.loaded / event.total) * 100),
          });
      };
      request.onload = () => {
        requests.current.delete(id);
        let body: {
          uuid?: string;
          errors?: { file?: string[] };
          message?: string;
        } = {};
        try {
          body = JSON.parse(request.responseText);
        } catch {}
        if (request.status === 201 && body.uuid)
          return patch(id, { status: "done", progress: 100, uuid: body.uuid });
        const fallback =
          request.status === 413
            ? `Files must be ${rules.max_mb} MB or smaller.`
            : request.status === 429
              ? "Too many uploads. Please try again later."
              : "Upload failed. Please try again.";
        patch(id, {
          status: "error",
          error: body.errors?.file?.[0] ?? fallback,
        });
      };
      request.onerror = () => {
        requests.current.delete(id);
        patch(id, {
          status: "error",
          error: "Upload failed. Check your connection and try again.",
        });
      };
      const data = new FormData();
      data.append("file", file);
      data.append("upload_session", uploadSession);
      request.send(data);
    },
    [patch, rules.max_mb, uploadUrl],
  );

  const add = useCallback(
    (files: FileList | File[]) => {
      // One session id per visitor visit, created on the first pick (not on render, to stay hydration-safe).
      const uploadSession = session || crypto.randomUUID();
      if (!session) setSession(uploadSession);
      const accepted = items.filter((item) => item.status !== "error").length;
      let slots = rules.max_files - accepted;

      const next: Item[] = [];
      for (const file of Array.from(files)) {
        const id = crypto.randomUUID();
        const problem =
          validate(file, rules) ??
          (slots <= 0
            ? `You can attach up to ${rules.max_files} files.`
            : null);
        if (problem) {
          next.push({
            id,
            name: file.name,
            size: file.size,
            status: "error",
            progress: 0,
            error: problem,
          });
          continue;
        }
        slots -= 1;
        next.push({
          id,
          name: file.name,
          size: file.size,
          status: "uploading",
          progress: 0,
        });
        send(id, file, uploadSession);
      }
      setItems((current) => [...current, ...next]);
    },
    [items, rules, send, session],
  );

  const remove = (item: Item) => {
    requests.current.get(item.id)?.abort();
    requests.current.delete(item.id);
    if (item.status === "done" && item.uuid) {
      void fetch(uploadUrl.replace(/\/?$/, "/") + item.uuid, {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ upload_session: session }),
      }).catch(() => undefined);
    }
    setItems((current) => current.filter((entry) => entry.id !== item.id));
  };

  const onDrop = (event: DragEvent<HTMLLabelElement>) => {
    event.preventDefault();
    setDragging(false);
    add(event.dataTransfer.files);
  };

  const counted = items.filter((item) => item.status !== "error").length;

  return (
    <div>
      <div className="mb-3 flex items-baseline justify-between text-sm">
        <span className="text-text-2 font-medium">
          Attachments <span className="text-dim font-normal">(optional)</span>
        </span>
        <span className="text-caption text-dim" aria-live="polite">
          {counted} of {rules.max_files} files
        </span>
      </div>

      <label
        onDragOver={(event) => {
          event.preventDefault();
          setDragging(true);
        }}
        onDragLeave={() => setDragging(false)}
        onDrop={onDrop}
        className={cn(
          "rounded-card bg-surface-input has-focus-visible:outline-accent flex cursor-pointer flex-col items-center gap-2.5 border border-dashed px-6 py-9 text-center transition-colors has-focus-visible:outline-2 has-focus-visible:outline-offset-2",
          dragging ? "border-accent bg-chip" : "border-accent-border",
        )}
      >
        <span className="rounded-tile bg-chip text-icon-soft flex size-11 items-center justify-center">
          <Upload className="size-5" strokeWidth={1.8} aria-hidden="true" />
        </span>
        <span className="text-ui text-text-2">
          <span className="text-link font-semibold">Choose files</span> or drag
          them here
        </span>
        <span id={hintId} className="text-caption text-dim">
          {rules.types
            .map((type) => type.toUpperCase())
            .filter((type) => type !== "JPEG")
            .join(", ")
            .replace(/, ([^,]*)$/, " or $1")}{" "}
          · up to {rules.max_mb} MB each · max {rules.max_files} files
        </span>
        <input
          ref={input}
          type="file"
          multiple
          aria-describedby={hintId}
          accept={rules.types.map((type) => `.${type}`).join(",")}
          className="sr-only"
          onChange={(event) => {
            if (event.target.files) add(event.target.files);
            event.target.value = "";
          }}
        />
      </label>

      {session
        ? items
            .filter((item) => item.uuid)
            .map((item) => (
              <input
                key={item.id}
                type="hidden"
                name="upload_uuids"
                value={item.uuid}
              />
            ))
        : null}
      {session ? (
        <input type="hidden" name="upload_session" value={session} />
      ) : null}

      {items.length > 0 ? (
        <ul className="mt-4 flex flex-col gap-2.5">
          {items.map((item) => (
            <li
              key={item.id}
              className={cn(
                "rounded-card bg-surface-input flex items-center gap-3.5 border p-3",
                item.status === "error"
                  ? "border-danger-border"
                  : "border-border-input",
              )}
            >
              <span
                className={cn(
                  "rounded-control flex size-10.5 shrink-0 items-center justify-center",
                  item.status === "error"
                    ? "bg-danger-tile text-danger"
                    : item.status === "done"
                      ? "bg-tile text-icon-soft"
                      : "bg-chip text-link",
                )}
              >
                <FileIcon
                  className="size-5"
                  strokeWidth={1.8}
                  aria-hidden="true"
                />
              </span>
              <div className="min-w-0 grow">
                <div className="text-ui text-text truncate font-medium">
                  {item.name}
                </div>
                {item.status === "done" ? (
                  <div className="text-caption text-success mt-0.5 flex items-center gap-1.5">
                    <Check
                      className="size-3.5"
                      strokeWidth={2}
                      aria-hidden="true"
                    />
                    <span>{formatSize(item.size)} · Uploaded</span>
                  </div>
                ) : null}
                {item.status === "uploading" ? (
                  <div className="mt-1.5 flex items-center gap-3">
                    <div
                      className="rounded-pill bg-border h-1.5 grow overflow-hidden"
                      role="progressbar"
                      aria-valuenow={item.progress}
                      aria-valuemin={0}
                      aria-valuemax={100}
                      aria-label={`Uploading ${item.name}`}
                    >
                      <div
                        className="rounded-pill bg-accent h-full transition-[width]"
                        style={{ width: `${item.progress}%` }}
                      />
                    </div>
                    <span className="text-caption text-dim w-9 text-end">
                      {item.progress}%
                    </span>
                  </div>
                ) : null}
                {item.status === "error" ? (
                  <div
                    role="alert"
                    className="text-caption text-danger mt-0.5 flex items-center gap-1.5"
                  >
                    <Icon name="alert" className="size-3.5 shrink-0" />
                    <span>{item.error}</span>
                  </div>
                ) : null}
              </div>
              <button
                type="button"
                onClick={() => remove(item)}
                aria-label={`Remove ${item.name}`}
                className="rounded-control text-dim hover:text-text flex size-9 shrink-0 items-center justify-center transition-colors"
              >
                <X className="size-4.5" strokeWidth={1.8} aria-hidden="true" />
              </button>
            </li>
          ))}
        </ul>
      ) : null}
    </div>
  );
}
