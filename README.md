# Gymnasiearbete
Detta är mitt gymnasiearbete i kursen Webbserverprogrammering 1.  
Syftet var att utveckla ett eget webbramverk i objektorienterad PHP samt en tillhörande demo-applikation (simpelt forum).  

All kod för ramverket finns under "Framework" medan "App" innehåller demo-applikationen. Demo-applikationen och specifikt "App.php" är en bra utgångspunkt för den som vill veta hur ramverket kan användas.

**Ramverkets innehåll**
- Router
  - Vanlig routing, t.ex. /about, /pages/about
  - Path variabler, t.ex. /api/users/{id}, /posts/{id}/progress
  - Grupper, t.ex. createGroup(“api”) där alla under-routes automatiskt får /api prefix
- Middlewares - kan läggas till globalt, i router-grupper eller på enskilda routes
- SQLite med egen kod som genererar simpla queries för create table, insert osv
- Template engine - Twig
- Dependency injection - Skapa t.ex. ett “AuthController” objekt dynamiskt genom att kolla vad den har för dependencies i konstruktorn.
- Mappande av förfrågningar till klasser, t.ex. “RegisterRequest” som innehåller string $username, string $password och string $confirm vilka sätts dynamiskt. Datan fås från query params alternativt JSON i body.
- Validering av förfrågningar, t.ex. att fältet “username” är required samt “max_length 16” inte får vara längre än 16 bokstäver.
- Klass för simpel validering av HTTP-strängar för att förebygga XSS och liknande.


För att testa demo-applikationen medföljer en databasfil med följande användare:  
**Användarnamn:** admin@example.com | **Lösenord:** admin123  
**Användarnamn:** user@example.com | **Lösenord:** user1234
