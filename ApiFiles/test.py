import requests
import json
import subprocess

url = "https://api.themoviedb.org/3/tv/popular?api_key=573e4dfa4b2525b966b6b81a1fc5de7b&language=en-US&page=1"
response = requests.request("GET", url)

f = open("rawData.json", 'w')
f.write(response.text)
f.close()

readyJson = {}

f = open("rawData.json")
data = json.load(f)

index = 1
for show in data["results"]:
    tempShowJson = {}
    tempShowJson["name"] = show["name"].replace("'","")
    tempShowJson["description"] = show["overview"].replace("'","")
    tempShowJson["img_url"] = "https://image.tmdb.org/t/p/w500" + str(show["poster_path"])
    tempShowJson["id"] = show["id"]
    idString = ""
    for gID in show["genre_ids"]:
        idString += str(gID) + ","
    tempShowJson["genre_ids"] = idString[:-1]
    readyJson["show"+str(index)] =  tempShowJson
    index += 1
    

    

#print(readyJson)
f = open("readyJson.json", "w")
json.dump(readyJson, f)
f.close()

subprocess.call(["php","-f","../dmzToDB.php", "readyJson.json"])