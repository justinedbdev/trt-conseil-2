# TRT Conseil

**This project is based on an exercise proposed during my training with Studi.**

## Technologies

This project uses the following technologies:
- HTML 5
- Bootstrap 5
- PHP 8.1 / Symfony 6 / TWIG
- MySQL

## Project presentation

**Subject of the project :**

The recruitment agency TRT Conseil, specializing in the hotel and catering industry, wants a tool allowing them to connect candidates and recruiters with the help of consultants, supervised by an administrator. The goal is to test if the demand is really present.

**Expected functionalities:**
- Account creation for recruiters and candidates with an email and a secure password, validated by a consultant
- Log in for all users
- Create a consultant by an administrator
- Complete its profile for candidates and recruiters
- Publish an recruitment ad for recruiters, validated by a consultant
- Apply for an ad for candidates, validated by a consultant

Only the back-end part has been worked on. A minimum of front-end was carried out but that wasn't the important thing in this project.

## Utilisation

To create my project, I used Symfony CLI with the -webapp option in order to have a global project base, containing all the tools made available by Symfony, in particular Composer, Doctrine, Profiler, Twig and Mailer. I installed and used the EasyAdmin bundle to create my administration interface and Maildev to manage sending and receiving emails in development mode.

To connect the database, this can be found in the .env file. You must add the data relating to BDD and your RDBMS in the DATABASE_URL.

```
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"
```

Use this command to start a local server :

```
$ symfony serve
```
