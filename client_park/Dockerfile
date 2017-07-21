FROM nodesource/node:4

RUN groupadd -r node \
   && useradd -m -r -g node node
USER node

RUN mkdir -p /home/node/app
WORKDIR /home/node/app

COPY . /home/node/app

RUN npm install --production
CMD ["node", "app.js"]
