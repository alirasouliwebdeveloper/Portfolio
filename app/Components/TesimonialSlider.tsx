import React from "react";
import Carousel from "react-multi-carousel";
import "react-multi-carousel/lib/styles.css";
import ClientReview from "./ClientReview";
import { testimonials } from "../data/Testimonials";

const responsive = {
  desktop: {
    breakpoint: { max: 3000, min: 1024 },
    items: 1,
    slidesToSlide: 1, // optional, default to 1.
  },
  tablet: {
    breakpoint: { max: 1024, min: 464 },
    items: 1,
    slidesToSlide: 1, // optional, default to 1.
  },
  mobile: {
    breakpoint: { max: 464, min: 0 },
    items: 1,
    slidesToSlide: 1, // optional, default to 1.
  },
};

const TesimonialSlider = () => {
  return (
    <Carousel
      additionalTransfrom={0}
      arrows={true}
      autoPlay={true}
      autoPlaySpeed={5000}
      centerMode={false}
      infinite
      responsive={responsive}
      itemClass="item"
    >
      {testimonials &&
        testimonials.map((testimonial) => {
          return (
            <ClientReview
              key={testimonial.id}
              image="/images/c1.jpg"
              rate={testimonial.rate}
              name={testimonial.fullName}
              role={testimonial.jobTitle}
              testimonial={testimonial.testimonial}
            />
          );
        })}
    </Carousel>
  );
};

export default TesimonialSlider;
