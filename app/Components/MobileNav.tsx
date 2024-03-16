import { XMarkIcon } from "@heroicons/react/16/solid";
import Link from "next/link";
import React from "react";

interface Props {
  nav: boolean;
  closeNav: () => void;
}

const MobileNav = ({ closeNav, nav }: Props) => {
  const navAnimation = nav ? "translate-x-0" : "translate-x-[-100%]";
  return (
    <div
      className={`fixed ${navAnimation} transform transition-all duration-300 top-0 lef-0 right-0 bottom-0 z-[10000] bg-[#09101a]`}
    >
      <div className="w-[100vw] h-[100vh] flex flex-col items-center justify-center">
        <Link onClick={closeNav} href="#home" className="nav-link-mobile">
          HOME
        </Link>
        <Link onClick={closeNav} href="#about" className="nav-link-mobile">
          ABOUT
        </Link>
        <Link onClick={closeNav} href="#services" className="nav-link-mobile">
          SERVICE
        </Link>
        <Link onClick={closeNav} href="#skills" className="nav-link-mobile">
          SKILLS
        </Link>
        <Link onClick={closeNav} href="#projects" className="nav-link-mobile">
          PROJECT
        </Link>
        <Link onClick={closeNav} href="#blog" className="nav-link-mobile">
          BLOG
        </Link>
        <Link onClick={closeNav} href="#contact" className="nav-link-mobile">
          CONTACT
        </Link>
      </div>
      <div
        onClick={closeNav}
        className="absolute z-[100000] cursor-pointer top-[2rem] right-[2rem] w-[2rem] h-[2rem] text-yellow-400"
      >
        <XMarkIcon />
      </div>
    </div>
  );
};

export default MobileNav;
