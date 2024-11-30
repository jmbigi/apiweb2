import qrcode

# URLs para generar los códigos QR
urls = [
    "https://play.google.com/store/apps/details?id=com.libraryscores.faristol_app",
    "https://apps.apple.com/es/app/faristol/id6472727065"
]

# Nombres de los archivos para guardar los códigos QR
output_files = ["faristol_android_qr.png", "faristol_ios_qr.png"]

# Generar los códigos QR
for url, output_file in zip(urls, output_files):
    # Crear un objeto QR
    qr = qrcode.QRCode(
        version=1,  # Tamaño del QR (1 es el más pequeño)
        error_correction=qrcode.constants.ERROR_CORRECT_L,  # Nivel de corrección de errores
        box_size=10,  # Tamaño de cada caja del código QR
        border=4,  # Ancho del borde
    )
    
    # Agregar la URL al QR
    qr.add_data(url)
    qr.make(fit=True)

    # Crear la imagen del QR
    img = qr.make_image(fill='black', back_color='white')
    
    # Guardar la imagen en el archivo especificado
    img.save(output_file)

print("Códigos QR generados y guardados como faristol_android_qr.png y faristol_ios_qr.png")
