module.exports = {
    apps: [
        {
            name: "main", // محیط اصلی (Production)
            script: "npm",
            args: "start",
            cwd: "/var/www/main", // مسیر برنچ main
            env: {
                NODE_ENV: "production",
                PORT: 80,
            },
        },
        {
            name: "develop", // محیط توسعه (Development)
            script: "npm",
            args: "start",
            cwd: "/var/www/develop", // مسیر برنچ feature/develop
            env: {
                NODE_ENV: "development",
                PORT: 3000,
            },
        },
    ],
};
