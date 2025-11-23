before starting you will need to read test.ts to understand what data is being sent in and what files will be expected to run (using the gameId value in data) once you understand what should happen run the workerman server via php start.`php start --watch -d > ./logs/server.log 2>&1`

, then run the test file by `bun run test.ts > ./logs/run.log` then examine the log. if you find errors then fix the errors and run `bun run test.ts > ./logs/run.log` command again until no more errors. ignore warnings, only focus on errors.
