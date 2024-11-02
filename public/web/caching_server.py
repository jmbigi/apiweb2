# caching_server.py
from http.server import HTTPServer, SimpleHTTPRequestHandler

class CachingHTTPRequestHandler(SimpleHTTPRequestHandler):
    def end_headers(self):
        self.send_header('Cache-Control', 'public, max-age=60')  # Cache de 1 minuto
        super().end_headers()

if __name__ == "__main__":
    port = 8080
    server_address = ('', port)
    httpd = HTTPServer(server_address, CachingHTTPRequestHandler)
    print(f"Servidor HTTP corriendo en http://localhost:{port}")
    httpd.serve_forever()
