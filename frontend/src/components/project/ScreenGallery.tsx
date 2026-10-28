"use client";

import { X, ZoomIn, ZoomOut } from "lucide-react";
import {
  useCallback,
  useEffect,
  useRef,
  useState,
  type PointerEvent as ReactPointerEvent,
  type ReactNode,
} from "react";
import { ChevronLeft, ChevronRight } from "@/components/ui/icons";
import { Media } from "@/components/ui/Media";
import { cn } from "@/lib/cn";
import type { Project } from "@/types/api";

type Screen = Project["screens"][number];

const SWIPE_PX = 50;
/** Zoom is offered only when the original is clearly bigger than it is shown. */
const ZOOM_THRESHOLD = 1.15;

/**
 * Project screenshots: a server-visible grid of thumbnails; each opens a full-screen gallery
 * (native <dialog>: Esc and focus handling built in) with prev/next, keyboard arrows and swipe.
 * A screenshot larger than the screen gets a zoom button and can be dragged around when zoomed.
 */
export function ScreenGallery({ screens }: { screens: Screen[] }) {
  const items = screens.filter(
    (screen): screen is Screen & { image: NonNullable<Screen["image"]> } =>
      screen.image !== null,
  );
  const dialog = useRef<HTMLDialogElement>(null);
  const [index, setIndex] = useState<number | null>(null);

  const open = (at: number) => {
    setIndex(at);
    dialog.current?.showModal();
  };

  const close = useCallback(() => dialog.current?.close(), []);

  const step = useCallback(
    (by: number) =>
      setIndex((current) =>
        current === null
          ? current
          : (current + by + items.length) % items.length,
      ),
    [items.length],
  );

  // The page behind the modal must not scroll; restore it however the dialog closes (Esc too).
  useEffect(() => {
    const element = dialog.current;
    if (!element) return;
    const onClose = () => {
      document.documentElement.classList.remove("overflow-hidden");
      setIndex(null);
    };
    element.addEventListener("close", onClose);
    return () => element.removeEventListener("close", onClose);
  }, []);

  useEffect(() => {
    if (index !== null)
      document.documentElement.classList.add("overflow-hidden");
  }, [index]);

  if (items.length === 0) return null;

  return (
    <>
      <ul className="tablet:grid-cols-2 mt-12 grid gap-8">
        {items.map((screen, at) => (
          <li key={`${screen.image.url}-${at}`}>
            <figure className="flex flex-col gap-3">
              <button
                type="button"
                onClick={() => open(at)}
                aria-label={`Open screenshot ${at + 1} of ${items.length}${screen.caption ? `: ${screen.caption}` : ""}`}
                className="group hover-card rounded-card-lg border-border bg-surface focus-visible:outline-accent relative block aspect-16/10 w-full cursor-zoom-in overflow-hidden border focus-visible:outline-2 focus-visible:outline-offset-4"
              >
                <Media
                  image={screen.image}
                  size="md"
                  fill
                  sheen
                  sizes="(min-width: 768px) 50vw, 100vw"
                />
                <span
                  aria-hidden="true"
                  className="rounded-pill bg-bg/80 text-text absolute end-3 bottom-3 flex size-9 items-center justify-center opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100"
                >
                  <ZoomIn className="size-4.5" strokeWidth={1.8} />
                </span>
              </button>
              {screen.caption ? (
                <figcaption className="text-meta text-dim">
                  {screen.caption}
                </figcaption>
              ) : null}
            </figure>
          </li>
        ))}
      </ul>

      <dialog
        ref={dialog}
        aria-label="Project screenshots"
        className="bg-bg/95 text-text backdrop:bg-bg/80 m-0 h-dvh max-h-none w-dvw max-w-none border-0 p-0"
        onClick={(event) => {
          // A click on the dark area around the image (the dialog itself) closes it.
          if (event.target === event.currentTarget) close();
        }}
      >
        {index !== null ? (
          <Viewer
            key={index}
            screen={items[index]}
            position={index + 1}
            total={items.length}
            onPrev={() => step(-1)}
            onNext={() => step(1)}
            onClose={close}
          />
        ) : null}
      </dialog>
    </>
  );
}

function Viewer({
  screen,
  position,
  total,
  onPrev,
  onNext,
  onClose,
}: {
  screen: Screen & { image: NonNullable<Screen["image"]> };
  position: number;
  total: number;
  onPrev: () => void;
  onNext: () => void;
  onClose: () => void;
}) {
  const stage = useRef<HTMLDivElement>(null);
  const picture = useRef<HTMLImageElement>(null);
  const [zoomable, setZoomable] = useState(false);
  const [zoomed, setZoomed] = useState(false);
  const [zoomSize, setZoomSize] = useState<{ width: number; height: number }>();
  const pointer = useRef<{
    x: number;
    y: number;
    left: number;
    top: number;
    moved: boolean;
  } | null>(null);

  const rtl = () => document.documentElement.dir === "rtl";

  const measure = useCallback(() => {
    const img = picture.current;
    if (!img || !img.naturalWidth || zoomed) return;
    setZoomable(
      img.naturalWidth > img.clientWidth * ZOOM_THRESHOLD ||
        img.naturalHeight > img.clientHeight * ZOOM_THRESHOLD,
    );
  }, [zoomed]);

  useEffect(() => {
    window.addEventListener("resize", measure);
    return () => window.removeEventListener("resize", measure);
  }, [measure]);

  // Arrow keys follow reading direction: in RTL the "next" image is to the left.
  useEffect(() => {
    const onKey = (event: KeyboardEvent) => {
      if (event.key !== "ArrowRight" && event.key !== "ArrowLeft") return;
      if (zoomed) return; // arrows pan the zoomed image instead
      const forward = (event.key === "ArrowRight") !== rtl();
      event.preventDefault();
      if (forward) onNext();
      else onPrev();
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [zoomed, onNext, onPrev]);

  const toggleZoom = (at?: { x: number; y: number }) => {
    const img = picture.current;
    const box = stage.current;
    if (!img || !box) return;
    if (zoomed) {
      setZoomed(false);
      return;
    }
    // Keep the clicked point (or the centre) under the cursor after zooming in.
    const rect = img.getBoundingClientRect();
    const fx = at ? (at.x - rect.left) / rect.width : 0.5;
    const fy = at ? (at.y - rect.top) / rect.height : 0.5;
    // Natural size, but at most twice the screen width: a 1920px-wide full-page capture stays
    // readable on a phone instead of opening at four screens wide.
    const width = Math.min(img.naturalWidth, box.clientWidth * 2);
    const height = (width / img.naturalWidth) * img.naturalHeight;
    setZoomSize({ width, height });
    setZoomed(true);
    requestAnimationFrame(() => {
      box.scrollLeft = fx * width - box.clientWidth / 2;
      box.scrollTop = fy * height - box.clientHeight / 2;
    });
  };

  const onPointerDown = (event: ReactPointerEvent<HTMLDivElement>) => {
    if (event.button !== 0) return;
    const box = stage.current;
    if (!box) return;
    pointer.current = {
      x: event.clientX,
      y: event.clientY,
      left: box.scrollLeft,
      top: box.scrollTop,
      moved: false,
    };
    box.setPointerCapture(event.pointerId);
  };

  const onPointerMove = (event: ReactPointerEvent<HTMLDivElement>) => {
    const start = pointer.current;
    const box = stage.current;
    if (!start || !box) return;
    const dx = event.clientX - start.x;
    const dy = event.clientY - start.y;
    if (Math.abs(dx) > 5 || Math.abs(dy) > 5) start.moved = true;
    if (zoomed) {
      box.scrollLeft = start.left - dx;
      box.scrollTop = start.top - dy;
    }
  };

  const onPointerUp = (event: ReactPointerEvent<HTMLDivElement>) => {
    const start = pointer.current;
    pointer.current = null;
    if (!start) return;
    const dx = event.clientX - start.x;
    if (!zoomed && start.moved && Math.abs(dx) > SWIPE_PX && total > 1) {
      // Swiping towards the end of the line shows the previous image (mirrored in RTL).
      if (dx < 0 !== rtl()) onNext();
      else onPrev();
      return;
    }
    // Pointer capture retargets events to the stage, so "on the image" is a bounds check.
    const img = picture.current;
    const rect = img?.getBoundingClientRect();
    const onImage =
      rect !== undefined &&
      event.clientX >= rect.left &&
      event.clientX <= rect.right &&
      event.clientY >= rect.top &&
      event.clientY <= rect.bottom;
    if (!start.moved && onImage && (zoomable || zoomed)) {
      toggleZoom({ x: event.clientX, y: event.clientY });
    }
  };

  const src = zoomed ? screen.image.url : (screen.image.lg ?? screen.image.url);

  return (
    <div className="flex h-full flex-col">
      <div className="tablet:px-6 flex items-center justify-between gap-4 px-4 py-3">
        <p className="text-meta text-muted tabular-nums" aria-live="polite">
          {position} / {total}
        </p>
        <div className="flex items-center gap-2">
          {zoomable || zoomed ? (
            <ToolbarButton
              label={zoomed ? "Zoom out" : "Zoom in"}
              onClick={() => toggleZoom()}
            >
              {zoomed ? (
                <ZoomOut className="size-5" strokeWidth={1.8} />
              ) : (
                <ZoomIn className="size-5" strokeWidth={1.8} />
              )}
            </ToolbarButton>
          ) : null}
          <ToolbarButton label="Close gallery" onClick={onClose}>
            <X className="size-5" strokeWidth={1.8} />
          </ToolbarButton>
        </div>
      </div>

      <div className="relative min-h-0 flex-1">
        <div
          ref={stage}
          onPointerDown={onPointerDown}
          onPointerMove={onPointerMove}
          onPointerUp={onPointerUp}
          onPointerCancel={() => (pointer.current = null)}
          className={cn(
            "h-full w-full select-none",
            zoomed
              ? "cursor-grab touch-none overflow-auto active:cursor-grabbing"
              : "tablet:px-20 flex touch-pan-y items-center justify-center overflow-hidden px-4",
          )}
        >
          {/* A plain <img>: zoom needs the original's natural size, which next/image hides. */}
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img
            ref={picture}
            src={src}
            alt={screen.image.alt}
            onLoad={measure}
            draggable={false}
            style={
              zoomed && zoomSize
                ? {
                    // Sized up front from the known dimensions so the zoomed view (and the
                    // scroll to the clicked point) is right before the full-size file arrives;
                    // the already-loaded smaller copy shows underneath until then.
                    width: zoomSize.width,
                    height: zoomSize.height,
                    backgroundImage: `url("${screen.image.lg ?? screen.image.url}")`,
                    backgroundSize: "100% 100%",
                  }
                : undefined
            }
            className={cn(
              zoomed
                ? "max-w-none"
                : "rounded-card max-h-full max-w-full object-contain",
              zoomable && !zoomed ? "cursor-zoom-in" : undefined,
              zoomed ? "cursor-zoom-out" : undefined,
            )}
          />
        </div>

        {total > 1 && !zoomed ? (
          <>
            <NavButton
              label="Previous screenshot"
              onClick={onPrev}
              className="tablet:start-5 start-2"
            >
              <ChevronLeft className="size-6" />
            </NavButton>
            <NavButton
              label="Next screenshot"
              onClick={onNext}
              className="tablet:end-5 end-2"
            >
              <ChevronRight className="size-6" />
            </NavButton>
          </>
        ) : null}
      </div>

      <p className="text-ui text-text-2 tablet:px-6 min-h-12 px-4 py-3 text-center">
        {screen.caption}
      </p>
    </div>
  );
}

function ToolbarButton({
  label,
  onClick,
  children,
}: {
  label: string;
  onClick: () => void;
  children: ReactNode;
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      aria-label={label}
      title={label}
      className="rounded-control border-border-input text-text-2 hover:border-accent hover:text-text focus-visible:outline-accent flex size-10 items-center justify-center border transition-colors focus-visible:outline-2 focus-visible:outline-offset-2"
    >
      {children}
    </button>
  );
}

function NavButton({
  label,
  onClick,
  className,
  children,
}: {
  label: string;
  onClick: () => void;
  className: string;
  children: ReactNode;
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      aria-label={label}
      className={cn(
        "rounded-pill bg-surface/90 border-border text-text hover:border-accent focus-visible:outline-accent absolute top-1/2 flex size-11 -translate-y-1/2 items-center justify-center border transition-colors focus-visible:outline-2 focus-visible:outline-offset-2",
        className,
      )}
    >
      {children}
    </button>
  );
}
