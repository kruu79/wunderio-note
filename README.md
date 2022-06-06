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
To add a new note send a POST request to `/note/add`.   
The request accepts JSON in the body of the request with a following structure:  
	```{
		title: "Sample title",
		text: "Sample text"
	}```

###### Get note by id
To get a note send a GET request to `/note/{id}`. It returns JSON formatted response:  
	```{
		"status":"success",
		"data":{
			"note":{
				"id":1,
				"title":"Sample title",
				"text":"Sample text.",
				"createdAt":"2022-06-01T12:15:33+00:00"
			}
		}
	}```

If note does not exist it returns response with 404 status code:  
	```{
		"data":{
			"id":"This note does not exist!"
		},
		"status":"fail"
	}```

###### Update note by id
To update title and text of a note send a PUT request to `/note/{id}`.  
It accepts same data format as "add new note" request.  

###### Delete a note by id
To delete a note send DELETE request to `/note/{id}`.  

###### Get notes
To get all notes send a GET request to `/notes`. By default newest notes are listed first. The request accepts addition query string parameters:
1. `limit=n` limits results to first n records.
2. `sort=oldest` lists oldest records first.
3. `search=query_sting` lists only notes that contain 'query_string' in the text field.  

## Web tests
1. Open phpMyAdmin and create "symfony_test" database.
2. Run mysql query to grant access to user "symfony" to the database - `GRANT ALL ON symfony_test.* TO symfony;`
2. To generate test database tables go to symfony project root and run - `./bin/console --env=test doctrine:schema:create`
3. Load test data in test database - `./bin/console --purge-with-truncate --env=test doctrine:fixtures:load`
4. Run the test suite - `./bin/phpunit tests/NoteControllerTest.php`  
