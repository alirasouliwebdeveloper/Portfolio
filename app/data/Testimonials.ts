export interface Testimonial {
  id: number;
  fullName: string;
  rate: number;
  jobTitle: string;
  testimonial: string;
}

export const testimonials: Testimonial[] = [
  {
    id: 1,
    fullName: "Sarah Adams Mohammad Rahmani",
    rate: 5,
    jobTitle: "Marketing Manager",
    testimonial:
      "I’ve had the pleasure of collaborating with Ali Rasouli on several projects. Their creativity and attention to detail are unmatched. Highly recommended!",
  },
  {
    id: 2,
    fullName: "Michael Brown",
    rate: 3,
    jobTitle: "Software Engineer",
    testimonial:
      "Ali Rasouli is a talented web developer. Their code is clean, efficient, and well-organized. It’s always a pleasure working with them.",
  },
  // {
  //   id: 2,
  //   fullName: "Ali",
  //   rate: 1,
  //   jobTitle: "Ali",
  //   testimonial: "Ali",
  // },
];
