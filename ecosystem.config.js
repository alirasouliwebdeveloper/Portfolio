module.exports = {
  apps: [
    {
      name: "Portfolio",
      script: "npm",
      args: "start",
      env: {
        PORT: 3000, // تغییر به پورت دلخواه
        NODE_ENV: "production",
      },
    },
  ],
};
