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
          <SkillsItem title="React Developer" year="2014 - 2019" />
          <SkillsItem title="Laravel Developer" year="2019 - 2023" />
          <SkillsLanguage
            skill1="html"
            skill2="css"
            skill3="css"
            level1="w-[91%]"
            level2="w-[88%]"
            level3="w-[88%]"
          />
        </div>
        <div>
          <SkillsItem title="React Developer" year="2014 - 2019" />
          <SkillsItem title="Laravel Developer" year="2019 - 2023" />
          <SkillsLanguage
            skill1="html"
            skill2="css"
            skill3="css"
            level1="w-[91%]"
            level2="w-[88%]"
            level3="w-[88%]"
          />
        </div>
      </div>
    </div>
  );
};

export default Skills;
