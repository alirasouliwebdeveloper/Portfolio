import { StarIcon } from "@heroicons/react/16/solid";
import Image from "next/legacy/image";
import React from "react";

interface Props {
  image: string;
  name: string;
  role: string;
  testimonial: string;
  rate: number;
}

const ClientReview = ({ image, name, role, testimonial, rate }: Props) => {
  const stars = (rate: number) => {
    let starIcons = [];
    for (let index = 1; index < 6; index++) {
      if (rate >= index) {
        starIcons.push({ color: "text-yellow-400" });
      } else {
        starIcons.push({ color: "text-gray-600" });
      }
    }
    return starIcons;
  };

  const starIcons = stars(rate);
  // console.log("starIcons", starIcons);
  return (
    <div className="flex flex-col text-center justify-center">
      <Image
        src={image}
        alt={name}
        width={100}
        height={100}
        objectFit="contain"
        className="mx-auto mb-[2rem] rounded-full"
      />
      <div className="flex items-center mx-auto">
        {starIcons.map(({ color }, index) => {
          return (
            <StarIcon key={index} className={`w-[2rem] h-[2rem] ${color}`} />
          );
        })}
      </div>

      <h1 className="text-[25px] text-white mt-[1rem]">{name}</h1>
      <p className="text-[18px] text-white mt-[0.5rem] opacity-75 mb-[1.4rem]">
        {role}
      </p>
      <p className="text-[16px] text-white opacity-50 w-[90%] md:w-[50%] mx-auto">
        {testimonial}
      </p>
    </div>
  );
};

export default ClientReview;
