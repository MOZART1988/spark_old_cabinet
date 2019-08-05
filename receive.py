#!/usr/bin/env python
import pika
import urllib.request
import json
import subprocess

connection = pika.BlockingConnection(pika.ConnectionParameters(
		host='localhost'))
channel = connection.channel()


channel.queue_declare(queue='hello')

def callback(ch, method, properties, body):
	url='https://cabinet.spark-logistics.kz/API/public/index.php/v1/leadid/'+body.decode()
	print(url)
	print()
	try:
		x = urllib.request.urlopen(url)
		j = json.loads(x.read().decode())
		print(j)
	except ValueError:
		print(body.decode()+" was not written. Trying to write it.")
		subprocess.Popen(["/usr/bin/php", "/var/www/html/API/send.php", body.decode()])

channel.basic_consume(callback,
					  queue='hello',
					  no_ack=True)

print(' [*] Waiting for messages. To exit press CTRL+C')
channel.start_consuming()