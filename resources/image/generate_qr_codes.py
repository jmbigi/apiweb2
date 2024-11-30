import qrcode
from PIL import Image, ImageDraw, ImageFont
import os

# URLs para generar los códigos QR
urls = [
    "https://play.google.com/store/apps/details?id=com.libraryscores.faristol_app",
    "https://apps.apple.com/es/app/faristol/id6472727065"
]

# Nombres de los archivos para guardar los códigos QR
output_files = ["faristol_android_qr.png", "faristol_ios_qr.png"]

# Texto a agregar
labels = ["Android", "iOS"]

# Crear los códigos QR
for url, output_file, label in zip(urls, output_files, labels):
    # Crear un objeto QR
    qr = qrcode.QRCode(
        version=1,  # Tamaño del QR (1 es el más pequeño)
        error_correction=qrcode.constants.ERROR_CORRECT_L,  # Nivel de corrección de errores
        box_size=5,  # Tamaño de cada caja del código QR
        border=4,  # Ancho del borde
    )
    
    # Agregar la URL al QR
    qr.add_data(url)
    qr.make(fit=True)

    # Crear la imagen del QR
    img = qr.make_image(fill='black', back_color='white')
    
    # Convertir la imagen a un objeto de Pillow para añadir el texto
    img = img.convert("RGB")
    draw = ImageDraw.Draw(img)

    # Seleccionar una fuente (se puede cambiar por un archivo de fuente .ttf si se desea)
    try:
        font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 12)
    except IOError:
        font = ImageFont.load_default()
        print("No cargó font.")

    # Obtener las dimensiones del texto con textbbox
    bbox = draw.textbbox((0, 0), label, font=font)
    text_width = bbox[2] - bbox[0]
    text_height = bbox[3] - bbox[1]

    # Centrar el texto horizontalmente
    text_x = (img.width - text_width) // 2
    text_y = 3  # Posición vertical del texto

    draw.text((text_x, text_y), label, font=font, fill="black")

    # Guardar la imagen con el texto en el borde
    img.save(os.path.join(os.path.dirname(__file__), output_file))

print("Códigos QR generados y guardados como faristol_android_qr.png y faristol_ios_qr.png")
