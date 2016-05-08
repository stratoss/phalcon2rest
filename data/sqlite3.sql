CREATE TABLE access_tokens (
  userId bigint NOT NULL,
  tokenId varchar NOT NULL,
  isRevoked tinyint NOT NULL DEFAULT '0',
  expiry int NOT NULL
);

CREATE TABLE "clients" (
  "id" bigint NOT NULL,
  "secret" varchar NOT NULL,
  "name" varchar NOT NULL,
  "redirect_url" varchar NOT NULL,
  "is_confidential" tinyint NOT NULL
);
INSERT INTO "clients" VALUES (1,'$2y$10$5m1jvrkBZDkCZDfyJrv0A.TlkETpwpWjzx29ZxzlolwGtBXaHOkJa','Super App','http://example.com/super-app',1);

CREATE TABLE "refresh_tokens" (
  "userId" bigint NOT NULL,
  "tokenId" varchar NOT NULL,
  "isRevoked" tinyint NOT NULL DEFAULT '0',
  "expiry" int NOT NULL
);

CREATE TABLE "users" (
  "id" bigint NOT NULL,
  "username" varchar NOT NULL,
  "password" varchar NOT NULL,
  "access" tinyint NOT NULL DEFAULT '1'
);

INSERT INTO "users" VALUES (1,'stan','$2y$10$8yjhRKQmDIXYl/pAbloBD.5vuGr/xkzCLeJCw5H5sycD8QbcDfZzC',1);

create table "books" (
  "id" bigint not null,
  "author" varchar not null,
  "title" varchar not null,
  "year" int not null
);

INSERT INTO "books" VALUES (1,'John Doe','Greatest book',2010);
INSERT INTO "books" VALUES (2,'John Doe','Book of books',2010);
INSERT INTO "books" VALUES (3,'Stanimir Stoyanov','OAuth2 with Phalcon',2016);