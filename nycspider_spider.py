from scrapy.http import FormRequest
from scrapy.spider import BaseSpider
from scrapy.http import Request
from bs4 import BeautifulSoup
import re
import sys

'''to call use scrapy crawl dmoz -a address={ADDRESS} -a borough={BOROUGH #}
boroughs are: 1 = Manhattan, 2 = Bronx, 3 = Brooklyn, 4 = Queens, 5 = Staten Island'''

class NYCSpider(BaseSpider):
    name = "nyc"
    start_urls = ["http://council.nyc.gov/html/members/members.shtml"]

    def __init__(self, address='', borough = 0):
        super(BaseSpider, self).__init__()
        self.address = address
        self.borough = borough

    def parse(self, response):
        return self.login(response)
        
    def login(self, response):
        return [FormRequest.from_response(response,
                                          formdata={'lookup_address': self.address, 'lookup_borough' : self.borough},
                                          formnumber = 1, callback=self.parse_evalPage)]

    def parse_evalPage(self, response):
        soup = BeautifulSoup(response.body)
        anchors =  [td.find('a') for td in soup.findAll('td', {"class":"nav_text"})]\
        for a in anchors:
            with open("~/recordedEmails.txt", 'a') as f:
                link = a['href']
                mailto_remove = re.compile(re.escape('mailto:'), re.IGNORECASE)
                link = mailto_remove.sub('', link)
                f.write(link)
                sys.stdout.write(a['href'])







