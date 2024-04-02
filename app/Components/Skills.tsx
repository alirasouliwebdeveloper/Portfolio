import React from "react";
import SkillsItem from "./SkillsItem";
import SkillsLanguage from "./SkillsLanguage";

const Skills = () => {
  return (
    <div id="skills" className="pt-[4rem] md:pt-[8rem] bp-[5rem] bg-[#09101a]">
      <h1 className="heading">
        Education & <span className="text-yellow-400">Skill</span>
      </h1>
      <div className="w-[80%] mx-auto pt-[4rem] md:pt-[8rem] grid grid-cols-1 md:grid-cols-2 gap-[2rem] items-center">
        <div>
          <SkillsItem
            title="WordPress Developer"
            year="2014 - Present"
            description="Popular CMS for web development. Customizable with themes, plugins. Supports rapid development, community resources aid troubleshooting, collaboration."
          />
          <SkillsLanguage
            skill1="html"
            skill2="css"
            skill3="php"
            level1="w-[91%]"
            level2="w-[88%]"
            level3="w-[88%]"
          />
        </div>
        <div>
          <SkillsItem
            title="React Developer"
            year="2019 - Present"
            description="JavaScript library for building user interfaces. Declarative, component-based approach enhances development efficiency."
          />
          <SkillsLanguage
            skill1="javascript"
            skill2="React"
            skill3="Tailwind"
            level1="w-[90%]"
            level2="w-[90%]"
            level3="w-[90%]"
          />
        </div>
        <div>
          <SkillsItem
            title="Laravel Developer"
            year="2017 - Present"
            description="PHP framework for web application development. Elegant syntax, robust features streamline development process."
          />
          <SkillsLanguage
            skill1="PHP"
            skill2="Blade"
            skill3="Tailwind"
            level1="w-[90%]"
            level2="w-[90%]"
            level3="w-[90%]"
          />
        </div>
      </div>
    </div>
  );
};

export default Skills;
