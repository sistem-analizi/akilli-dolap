#include <Wire.h>
#include <Keypad_I2C.h>
#include <Keypad.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <WiFi.h>
#include <HTTPClient.h> 

#include <Fonts/FreeSans9pt7b.h>

#define I2CADDR 0x20 
#define OLED_ADDR 0x3C
#define BUZZER_PIN 19 

const char* ssid = "DolapProjesi"; 
const char* password = "123456789";
String serverIP = "192.168.137.1"; 

const byte ROWS = 4; 
const byte COLS = 3; 
char keys[ROWS][COLS] = {
  {'1','2','3'},
  {'4','5','6'},
  {'7','8','9'},
  {'*','0','#'}
};
byte rowPins[ROWS] = {0, 1, 2, 3}; 
byte colPins[COLS] = {4, 5, 6};    
Keypad_I2C customKeypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS, I2CADDR);

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET    -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

String dolapSifreleri[6] = {"1111", "2222", "3333", "4444", "5555", "6666"}; 
int dolapDoluMu[6] = {0, 0, 0, 0, 0, 0}; 

int kirmiziPins[6] = {33, 25, 14, 2, 23, 4};
int yesilPins[6]   = {32, 26, 27, 15, 18, 5};

int asama = 0;         
int secilenDolap = -1; 
String girilenSifre = ""; 
unsigned long sonGuncellemeZamani = 0; 

void setup() {
  Serial.begin(115200);
  Wire.begin(); 
  customKeypad.begin(); 

  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  for (int i = 0; i < 6; i++) {
    pinMode(kirmiziPins[i], OUTPUT);
    pinMode(yesilPins[i], OUTPUT);
  }

  if(!display.begin(SSD1306_SWITCHCAPVCC, OLED_ADDR)) {
    Serial.println(F("OLED baslatilamadi!"));
    for(;;);
  }

  display.clearDisplay();
  display.fillRect(0, 0, 128, 64, WHITE); 
  display.setTextColor(BLACK); 
  display.setTextSize(2);
  display.setCursor(10, 10);
  display.println("GYM");
  display.setCursor(10, 35);
  display.println("LOCKER");
  display.display();
  delay(1500);

  display.clearDisplay();
  display.setTextColor(WHITE);
  display.setTextSize(1);
  display.setCursor(10, 25);
  display.println("Sisteme Baglaniliyor");
  display.display();

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  basariSesi(); 
  verileriCek(); 
  ekraniGuncelle(); 
}

void loop() {
  if (asama == 0 && (millis() - sonGuncellemeZamani > 5000)) {
    verileriCek();
    sonGuncellemeZamani = millis();
  }

  char key = customKeypad.getKey();

  if (key) {
    tusSesi(); 

    if (key == '*') {
      sistemiSifirla();
      return; 
    }

    if (asama == 0) {
      if (key >= '1' && key <= '6') {
        secilenDolap = key - '0'; 
        asama = 1; 
        ekraniGuncelle();
      } else {
        hataSesi(); 
        bilgiEkraniGoster("Gecersiz Dolap!", "");
        delay(2000);
        sistemiSifirla();
      }
    }
    
    else if (asama == 1) {
      if (key == '#') {
        if (girilenSifre == dolapSifreleri[secilenDolap - 1]) {
          basariSesi(); 
          bilgiEkraniGoster("Kilit Acildi", "Kapak Acik");
          
          for(int j = 0; j < 3; j++) {
            digitalWrite(kirmiziPins[secilenDolap - 1], LOW);
            digitalWrite(yesilPins[secilenDolap - 1], HIGH); 
            delay(250);
            digitalWrite(yesilPins[secilenDolap - 1], LOW); 
            delay(250);
          }
          

          digitalWrite(yesilPins[secilenDolap - 1], HIGH); 
          delay(4000); 
          

          ledleriGuncelle(); 
          
        } else {

          hataSesi(); 
          bilgiEkraniGoster("Hatali Sifre", "Reddedildi");
          

          for(int j = 0; j < 5; j++) {
            digitalWrite(kirmiziPins[secilenDolap - 1], LOW);
            delay(100);
            digitalWrite(kirmiziPins[secilenDolap - 1], HIGH);
            delay(100);
          }
          delay(1000);
        }
        sistemiSifirla(); 
      } 
      else {
        girilenSifre += key; 
        ekraniGuncelle();
      }
    }
  }
}

void tusSesi() { digitalWrite(BUZZER_PIN, HIGH); delay(50); digitalWrite(BUZZER_PIN, LOW); }
void basariSesi() { digitalWrite(BUZZER_PIN, HIGH); delay(100); digitalWrite(BUZZER_PIN, LOW); delay(100); digitalWrite(BUZZER_PIN, HIGH); delay(150); digitalWrite(BUZZER_PIN, LOW); }
void hataSesi() { digitalWrite(BUZZER_PIN, HIGH); delay(500); digitalWrite(BUZZER_PIN, LOW); }

void verileriCek() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin("http://" + serverIP + "/akillidolap/api.php");
    if (http.GET() > 0) {
      String payload = http.getString();
      int dIdx = 0, sIdx = 0;
      for (int i = 0; i < payload.length(); i++) {
        if (payload.charAt(i) == ',') {
          String parca = payload.substring(sIdx, i); 
          int tire = parca.indexOf('-');
          if (tire > 0 && dIdx < 6) {
            dolapSifreleri[dIdx] = parca.substring(0, tire);
            dolapDoluMu[dIdx] = parca.substring(tire + 1).toInt();
          }
          sIdx = i + 1; dIdx++;
        }
      }
      
      ledleriGuncelle(); 
      if (asama == 0) {
        ekraniGuncelle(); 
      }
    }
    http.end();
  }
}


void ledleriGuncelle() {
  for (int i = 0; i < 6; i++) {

    digitalWrite(kirmiziPins[i], HIGH); 
    digitalWrite(yesilPins[i], LOW);
  }
}

void ekraniGuncelle() {
  display.clearDisplay();
  display.setFont(); 
  
  if (asama == 0) {
    for (int i = 0; i < 6; i++) {
      int col = i % 3; 
      int row = i / 3; 
      
      int x = col * 42; 
      int y = row * 32; 

      if (dolapDoluMu[i] == 1) {
        display.fillRect(x, y, 41, 31, WHITE);
        display.setTextColor(BLACK, WHITE);
      } else {
        display.drawRect(x, y, 41, 31, WHITE);
        display.setTextColor(WHITE, BLACK);
      }

      display.setCursor(x + 12, y + 6);
      display.print("D"); display.print(i + 1); 
      
      display.setCursor(x + 8, y + 18);
      if (dolapDoluMu[i] == 1) {
        display.print("DOLU");
      } else {
        display.print("BOS");
      }
    }
  } 
  else if (asama == 1) {
    display.fillRect(0, 0, 128, 16, WHITE); 
    display.setTextColor(BLACK, WHITE);     
    display.setCursor(35, 4);
    display.print("DOLAP 0"); display.print(secilenDolap);
    
    display.setTextColor(WHITE, BLACK);     
    display.setFont(&FreeSans9pt7b);
    display.setCursor(5, 45);
    display.print("Sifre: ");
    for(int i=0; i<girilenSifre.length(); i++){
      display.print("*");
    }
  }
  display.display();
}

void bilgiEkraniGoster(String satir1, String satir2) {
  display.clearDisplay();
  
  display.fillRect(0, 0, 128, 16, WHITE); 
  display.setTextColor(BLACK, WHITE);     
  display.setFont();                      
  display.setCursor(25, 4);
  display.print("SISTEM MESAJI");

  display.setTextColor(WHITE, BLACK);
  display.setFont(&FreeSans9pt7b);
  display.setCursor(5, 38);
  display.println(satir1);
  display.setCursor(5, 58);
  display.println(satir2);
  
  display.display();
}

void sistemiSifirla() {
  asama = 0; secilenDolap = -1; girilenSifre = "";
  ledleriGuncelle(); ekraniGuncelle();
}