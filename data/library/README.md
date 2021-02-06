# Library files index
The is a single file for each users library.
Each file contains an array for the movies,
and one for the shows. 

## ID
The id var tells you the TMDB id of the title.

## Path
The path var tells you what the S3 path to the
title is.

## Watch
The watch var tells you how many secons of
the title you have seen. If the var is
negative (like for example -1) then that
means you have completed the title. 

## Watch on seasons
If it is a show then the watch var will contain
a key array where the key is the key of the
episonde and season, like this "1-2" for
season 1 episode 2. The value for the key works
the exact same way as in a movie watch var.

## Languages
The languages var tells you all the supported
language files. All movies are named "main.mp4",
but the different languages are appended to the
name, like this: "main-en.mp4", or "main-no.mp4".

## CC
The closed captions var tells you the supported
cc files.