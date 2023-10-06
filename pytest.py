import serial
import time
import mysql.connector
import datetime
 
s = serial.Serial('/dev/ttyACM0', 9600)
s2 = serial.Serial('/dev/ttyACM1', 9600)
s.isOpen()
s2.isOpen()
time.sleep(5)
mydb = mysql.connector.connect(host="localhost", user="esel",  password="pass", database="Wasserstand")
mycursor = mydb.cursor()

warnung = 0
sqlStatuscheck = "SELECT statusaenderung FROM Warnnungen ORDER BY id DESC LIMIT 1"

sqlWerte = "INSERT INTO Pegel (timestamp, pegel, sensor) VALUES (%s, %s, %s)"
sqlWarnung = "INSERT INTO Warnungen (statusaenderung, timestamp) VALUES (%s, %s)"
sqlDurchschnitt = "INSERT INTO durchschnitt (timestamp, pegel) VALUES (%s, %s)"

try:
    mycursor.execute(sqlStatuscheck)
    myresult = mycursor.fetchall()
    if myresult[0][0] == "AN":
        warnung = 1
    while True:
        response = s.readline()
        response2 = s2.readline()
        decoded = int(response.decode())
        decoded2 = int(response2.decode())
        time = datetime.datetime.now()
        summe = decoded + decoded2
        avg = summe / 2
        if time.second == 0:
            werteVal = (time.strftime("%Y-%m-%d %H:%M:%S"), decoded, "ACM0")
            werteVal2 = (time.strftime("%Y-%m-%d %H:%M:%S"), decoded2, "ACM1")
            werteVal3 = (time.strftime("%Y-%m-%d %H:%M:%S"), avg)
            mycursor.execute(sqlWerte, werteVal)
            mycursor.execute(sqlWerte, werteVal2)
            mycursor.execute(sqlDurchschnitt, werteVal3)
            mydb.commit()
        if avg > 300 and warnung == 0:
            warnung = 1
            warnungVal = ("AN", time.strftime("%Y-%m-%d %H:%M:%S"))
            mycursor.execute(sqlWarnung, warnungVal)
            mydb.commit()
        elif avg <= 300 and warnung == 1:
            warnung = 0
            warnungVal = ("AUS", time.strftime("%Y-%m-%d %H:%M:%S"))
            mycursor.execute(sqlWarnung, warnungVal)
            mydb.commit()
            
except KeyboardInterrupt:
    s.close()
    mycursor.close()
