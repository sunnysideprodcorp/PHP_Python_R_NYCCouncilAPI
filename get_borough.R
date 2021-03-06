library(RJSONIO)
options(warn=-1)

# initialize URL lookup
api_key_district = 
lat = argv[1] 
lon = argv[2] 
prep_url = paste0("http://api.nytimes.com/svc/politics/v2/districts.json?lat=", lat, "&lng=", lon, "&api-key=", api_key_district)


# parse data from URL and return as soon as you find the borough
# NYT API returns JSON without a guaranteed order, one element in list usually includes the appropriate borough
getData <- function(url){

  raw.data <- readLines(url) 
  rd       <- fromJSON(raw.data)
  df       <- rd$results

  # loop until you find the borough, write that, and return
  for(i in 1:length(df)){
    if(df[[i]]["level"] == "Borough"){
      borough = unname(df[[i]]["district"])
      write(borough, stdout())
      break
    }
  }


}

# get the data
getData(prep_url)

