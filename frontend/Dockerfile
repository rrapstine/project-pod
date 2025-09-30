FROM docker.io/oven/bun

WORKDIR /app

COPY package.json bun.lock ./

RUN bun install
ENV PATH="/app/node_modules/.bin:$PATH"

COPY . .

EXPOSE 3000

CMD ["bun", "run", "dev", "--", "--host", "0.0.0.0"]
