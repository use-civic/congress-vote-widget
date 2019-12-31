

## Congress Vote Graphic Demo

This repo shows the code used to generate graphics for a Medium article:

**> Link to article**

Here's what the output looks like:

![Example output](output-example.png?raw=true "Example output")

### About This Repo

The point of this repo is solely to create graphics for the Medium article.

The code takes an input file and outputs a voting graphic. The code is _only_ for House votes, not Senate.

As this is a demo, no support in setup or Issue management is available. 

If you'd like to work on the project, you can
get notified when the entire thing is open sourced! You'll only be emailed once after subscribing:

https://mailchi.mp/62de16d96a49/civic-launch


### Setup

1. Have PHP and Composer on your system.
2. Check the code out
3. Run `composer install`
4. Execute the `index.php` file - whether by pointing it to a web server, or executing it over a command line

At the end of step 4 you will have a visual representation of the voting file found at `/data/115th-congress-h3-2017.json`


### Broad Overview

There are three main concepts in generating an image:

| Step | File | Responsibility |  
|---|----------|-------------|
| #1 | VoteParser.php | Parses the JSON file and creates metadata around it. |
| #2 | GraphicGenerator.php | Acts as a mediator between the data (VoteParser.php output) and the PHP GD graphic generation (VoteGraphic.php) |
| #3 | VoteGraphic.php | This class does the actual drawing of the chart |

The three files do their best to not be aware of each other, and remain functional (input -> output) with no dependencies.

One more time for those in the back: this is a demo and not meant to be supported. Although, it's nicely commented and you should be
able to follow the logic pretty well!

This code is public domain - do anything you want. Happy coding!
