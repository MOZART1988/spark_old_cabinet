from urllib.request import urlopen
import sys
url='http://45.32.153.55/API/public/index.php/v1/leadid/' + sys.argv[1]
html = urlopen(url)