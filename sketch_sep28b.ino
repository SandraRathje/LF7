void setup(){
    Serial.begin(9600);
}
 
void loop(){
    //if (Serial.available()) {
        //byte nr = Serial.read();
        int value = analogRead(1);
        Serial.println(value);
        delay(1000);
    //}
}
