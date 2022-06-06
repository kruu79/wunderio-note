# Note API

Symfony based note API. Provides JSON API endpoints to create, read, update and delete notes.

## Setup

1. Install [docker](https://www.docker.com/get-started)
2. Start up docker compose instance - `docker-compose up --build -d`
3. Gain access to php bash shell - `docker exec -it php bash`
4. Go to root of symfony project - `cd code`
5. Install dependencies - `composer install`
5. Create note table in database - `./bin/console doctrine:migrations:migrate`
6. Open site in browser [localhost:8001](http://localhost:8001)
7. Open phpMyAdmin in brower [localhost:8081](http://localhost:8081) (user: 'root', password: 'root')


## Usage

###### Add new note
To add a new note send POST request to [localhost:8001/note/add](http://localhost:8001/note/add). The request accepts JSON in the body of the request with following structure:
	`{
		title: "Sample title",
		text: "Sample text"
	}`

###### Get note by id
To get a note send GET request to [localhost:8001/note/{id}](http://localhost:8001/note/{id}). It will return JSON formatted response:
	`{
		"status":"success",
		"data":{
			"note":{
				"id":1,
				"title":"Sample title",
				"text":"Sample text.",
				"createdAt":"2022-06-01T12:15:33+00:00"
			}
		}
	}`
If note does not exist it will return response with 404 status code:
	`{
		"data":{
			"id":"This note does not exist!"
		},
		"status":"fail"
	}`

###### Put an update to note by id
To update title and text of a note send PUT request to [localhost:8001/note/{id}](http://localhost:8001/note/{id}). It accepts same data format as "add new note" request.

###### Delete a note by id
To delete a note send DELETE request to [localhost:8001/note/{id}](http://localhost:8001/note/{id}).

###### Get notes
To get all notes send GET request to [localhost:8001/notes](http://localhost:8001/notes). By default newest notes are displayed first. The request can accept addition query string parameters:
1. `limit=n` will limit results to first n records.
2 `sort=oldest` will display oldest records first.
3 `search=query_sting` will display only notes that contain 'query_string' in the text field.


## Web tests
1. Open phpMyAdmin and create "symfony_test" database.
2. Run following mysql query to grant access to user "symfony" to the database - `GRANT ALL ON symfony_test.* TO symfony;`
2. To generate test database tables go to symfony project root and run - `./bin/console --env=test doctrine:schema:create`
3. Load test data is test database - `./bin/console --purge-with-truncate --env=test doctrine:fixtures:load`
4. Run the test suite - `./bin/phpunit tests/NoteControllerTest.php`
