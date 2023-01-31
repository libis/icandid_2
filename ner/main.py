import agensgraph
import json

from flask import Flask
from flask_restful import Resource, Api, request


app = Flask(__name__)
api = Api(app)
conn = agensgraph.connect("host=agensgraph dbname=agens user=agensreader password=agensreader")
conn.set_client_encoding('UTF8')
cur = conn.cursor()
cur.execute("SET graph_path=ner_graph;")

class Query(Resource):

    def get(self):
        return "Hello World !"
	
    def post(self):
#        timeout van DB veroorzaakt soms problemen, dus even reconnecten
        conn = agensgraph.connect("host=agensgraph dbname=agens user=agens password=agens")
        conn.set_client_encoding('UTF8')
        cur = conn.cursor()
        cur.execute("SET graph_path=ner_graph;")

        request.get_data()

        cur.execute(request.data)
        result = cur.fetchall()		
	       
        return result


api.add_resource(Query, '/graphquery')

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000,debug=True)


