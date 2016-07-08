# -*- coding: utf-8 -*-

import RPi.GPIO as GPIO
import time
import argparse
import glob
import grp
import os
import pwd
import re
import keyboardleds

from espeak import espeak

espeak.set_voice("pt")
espeak.set_parameter(7,5)

TRIG = 16
ECHO = 18
RELE = 7

DISTANCIA = 200
ledkit=''

def setupKeyLeds():
  global ledkit
  event_device = glob.glob('/dev/input/by-path/*-event-kbd')[0]
  ledkit = keyboardleds.LedKit(event_device)
  
  uid = pwd.getpwnam('nobody').pw_uid
  gid = grp.getgrnam('nogroup').gr_gid
    
def setupPorts():
  global TRIG, ECHO, RELE

  GPIO.setmode(GPIO.BOARD)
  GPIO.setup(RELE, GPIO.OUT)
  GPIO.setup(TRIG,GPIO.OUT)
  GPIO.setup(ECHO,GPIO.IN)
 
def keyLed(l, op):
  global ledkit
  
  led = getattr(ledkit, l+'_lock')
  if (op == 'on'):
    led.set()
  if (op == 'off'):
    led.reset()
    
def distancia():
  global TRIG, ECHO

  GPIO.output(TRIG,False)
  time.sleep(0.05)
 
  GPIO.output(TRIG, True)
  time.sleep(0.00001)
  GPIO.output(TRIG, False)
 
  while GPIO.input(ECHO)==0:
        pulse_start = time.time()
 
  while GPIO.input(ECHO)==1:
        pulse_end = time.time()
 
  pulse_duration = pulse_end - pulse_start
 
  distance = pulse_duration * 17150
  return round(distance, 2)

def monitorOff():
  keyLed('caps', 'on')
  keyLed('num', 'on')
  keyLed('scroll', 'on')
  time.sleep(1)
  keyLed('scroll', 'off')
  time.sleep(1)
  keyLed('caps', 'off')
  time.sleep(1)
  keyLed('num', 'off')
  espeak.synth("Vou tirar uma soneca")
  time.sleep(2)
  monitor('off')

def monitor(op):
  global RELE

  if (op == 'on'):
    GPIO.output(RELE, GPIO.LOW)
    print 'ON'
  if (op == 'off'):
    GPIO.output(RELE, GPIO.HIGH)
    print 'OFF'


def start():
  t = 0
  d = 0
  f = 1
  monitorOff()
  status = 'off'
 
  while(1):
 
    dis = distancia()

    if (status == 'off'):
      print 'D1: %s' % dis
      keyLed('num', 'on')
      f = 0.7
      if (dis > DISTANCIA):
        t = 0
    if (dis < DISTANCIA and status == 'off'):
      print 'D2: %s' % dis
      keyLed('caps', 'on')
      f = 0.4
    if (dis < DISTANCIA/2 and status == 'off'):
      print 'D3: %s' % dis
      keyLed('scroll', 'on')
      f = 0.2
      t = t + 1
    
    if (t == 5 and status == 'off'):
      monitor('on')
      status = 'on'
      time.sleep(10)
      print 'fala'
      espeak.synth("Bem vindo! Este é o Coice, Quiosque para Conscientização Eleitoral")
      time.sleep(10)
      espeak.synth("ah!!!! Eu não sou o istifem róuquim")

      f = 1
      t = 0

    time.sleep(f)

    keyLed('num', 'off')
    keyLed('caps', 'off')
    keyLed('scroll', 'off')
    
    if (status == 'on'):
      t = t + 1
      if (dis < DISTANCIA/2):
        d = 1
      print 'ON: t-%s d-%s ' % (t, d)

    if (t > 20):
      if (d == 0):
        monitorOff()
        status = 'off'
        print 'Deligar'
      t = 0
      d = 0
 
if __name__ == '__main__':
  setupPorts()
  setupKeyLeds()
  start()
