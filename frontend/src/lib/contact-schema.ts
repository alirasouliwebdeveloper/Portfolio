import { z } from "zod";

/** Field rules shared by the contact form (client hints) and the server action (authoritative copy is in Laravel). */
export const contactSchema = z.object({
  name: z
    .string()
    .trim()
    .min(1, "Please enter your name.")
    .max(100, "Name must be 100 characters or fewer."),
  email: z
    .string()
    .trim()
    .min(1, "Please enter your email.")
    .email("Enter a valid email address.")
    .max(255),
  company: z.string().trim().max(150).optional(),
  phone: z.string().trim().max(50).optional(),
  need: z.string().trim().min(1, "Please choose what you need.").max(100),
  budget: z.string().trim().max(100).optional(),
  timeline: z.string().trim().max(100).optional(),
  message: z
    .string()
    .trim()
    .min(10, "Please write at least 10 characters.")
    .max(5000, "Please keep it under 5000 characters."),
  service_slug: z.string().trim().max(100).optional(),
  upload_session: z.string().trim().max(64).optional(),
  upload_uuids: z.array(z.string().uuid()).max(5).optional(),
});

export type ContactInput = z.infer<typeof contactSchema>;

export type ContactState = {
  status: "idle" | "success" | "error";
  name?: string;
  message?: string;
  fieldErrors?: Partial<Record<keyof ContactInput | "turnstile", string>>;
};
