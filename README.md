# Actions (beta)
[![N|Solid](https://g3links.com/actions/g3links_brand.png)](https://g3links.com/actions)

Actions is a secure PHP  collaboration website tool for groups and/or individuals; well suited for registering tasks, tracking issues, customer complaints or just as messenger.
### Foundations based in three elements:
- **User:** person or identity registered with a valid email (verification is required).  
- **Action:** make reference to messages, events, follow-ups, comments, tracking, forums.  
- **Project:** contains users and actions controlled with customizable security rules by project (e.g.: project can be public or private, and specific user can only view actions).
### Typical deployment:
- **Private:** host the site and data in a private network, ake full control of users and security.
- **Private paid service (commercial):** host the site for private usage, charging a fee for backups and data.
- **Public free service:** host the site for public usage.
- **A mix:** a combination from all of the above.
### Some features!
- **faster communication:** invite external users to collaborate and set the preferable security level.
- **connect to multiple services:** whatever the role,  collaborating (invited) or owner (personal or inviting); you can link all services in a single view for easy management.
- **alerts and tracking:** subscribe to actions to receive notifications on top of the standard system.
- **create teams:** group users in teams to broadcast, assign and communicate faster.
### Installation
- **[SQLite](https://www.sqlite.org/index.html)**: SQL database engine with PDO extensions.
- **[G3 links data](https://github.com/g3links/data)**: setup database and config definitions (follow instructions)
- **MBString**: enable the php extension ‘mbstring’.
- Get dependecies using composer.
```sh
$ composer upddate --no-dev
```
