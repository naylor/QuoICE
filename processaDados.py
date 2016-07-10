#!/usr/bin/env python
# -*- coding: utf-8 -*-

import urllib2, zipfile, os
from collections import defaultdict
from pprint import pprint
import urllib
from StringIO import StringIO
import gzip
import warnings, MySQLdb
warnings.filterwarnings('error', category=MySQLdb.Warning)
import socket
import sys
import shutil
from collections import Counter
import re

#DEFAULT
con = ''

#----------------------------------------------------------------
inicio = 2006
userMysql = 'root'
userPass = open(".password").readline().rstrip()

estados = [
	'AC', 'AL', 'AM', 'AP', 'BA', 'BR', 'CE', 'DF', 'ES', 
	'GO', 'MA', 'MG', 'MS', 'MT', 'PA', 'PB', 'PE', 'PI', 
	'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'
]
estados.sort
destFolder = 'dados_ste/'
socket.setdefaulttimeout(300) #in seconds.
anosSemPadrao = ['2006', '2008', '2010']
#----------------------------------------------------------------

def truncate(string, width):
    if len(string) > width:
        string = string[:width-3] + '...'
    return string
    
def progress(count, total, suffix=''):
    bar_len = 40
    filled_len = int(round(bar_len * count / float(total)))

    percents = round(100.0 * count / float(total), 1)
    bar = '=' * filled_len + ' ' * (bar_len - filled_len)

    sys.stdout.write('[%s] %s%s (%s)\r' % (bar, percents, '%', suffix))
    sys.stdout.flush()

def connect():
	global con
	
	# create connection with database
	con = MySQLdb.connect('localhost', userMysql, userPass)
	con.autocommit(False)
	con.select_db('projeto')

def disconnect():
	global con

	con.close()

def commit():
	global con
	
	con.commit()
			
def executeSQL(SQL, ARGS = '', lastId = 0):
	global con

	cursor = con.cursor()

	try:
		if (ARGS != ''):
			cursor.execute(SQL, ARGS)
		else:
			cursor.execute(SQL)
		
		commit()
		
		cursor.close()
	except MySQLdb.IntegrityError, e: 
		cursor.close()
		print SQL
		pass
	except MySQLdb.Error, e:
		print SQL
		cursor.close()
	except MySQLdb.Warning, e:
		cursor.close()
		print SQL
		pass

	if (lastId == 1):
		return cursor.lastrowid
	return 1

def selectSQL(SQL, ARGS = ''):
	global con

	cursor = con.cursor()
	
	try:
		if (ARGS != ''):
			cursor.execute(SQL, ARGS)
		else:
			cursor.execute(SQL)

		result = cursor.fetchall()
	except MySQLdb.IntegrityError, e: 
		cursor.close()
		print "1:"+SQL
		print e
		exit(1)
	except MySQLdb.Error, e:
		print "2:"+SQL
		cursor.close()
		print e
		exit(1)
	except MySQLdb.Warning, e:
		cursor.close()
		print "3:"+SQL
		print e
		exit(1)
		
	cursor.close()
	
	return (result, cursor.rowcount)

def deleteTop10(ano):
	SQL = "DELETE FROM topCandidatos WHERE ano = '%s'" % (ano)
	executeSQL(SQL)
	SQL = "DELETE FROM topPartidos WHERE ano = '%s'" % (ano)
	executeSQL(SQL)
	SQL = "DELETE FROM topDoador WHERE ano = '%s'" % (ano)
	executeSQL(SQL)
	SQL = "DELETE FROM topBens WHERE ano = '%s'" % (ano)
	executeSQL(SQL)

	return 1
	
def createTop10(ano, estado):
	if (estado != 'BR'):
		q = "AND c.estado = '"+estado+"'"
	else:
		q = ''
		
	SQL = "INSERT INTO topCandidatos (cpf, ano, valor, tipo, estado) \
			SELECT c.cpf, c.ano, SUM(valor), 'receita', '%s' \
			FROM candReceitas r, candidato c, partido p, candCargo ca \
			WHERE r.id = c.id \
			AND r.ano = c.ano \
			AND p.codigo = c.partido \
			AND c.cargo = ca.codigo \
			AND c.ano = %s \
			AND c.situacao <> '2º TURNO' \
			%s \
			GROUP BY 1,2 \
			ORDER BY SUM(valor) \
			DESC LIMIT 10" % (estado, ano, q)
	executeSQL(SQL)
	
	SQL = "INSERT INTO topCandidatos (cpf, ano, valor, tipo, estado) \
			SELECT c.cpf, c.ano, SUM(valor), 'despesa', '%s' \
			FROM candDespesas r, candidato c, partido p, candCargo ca \
			WHERE r.id = c.id \
			AND r.ano = c.ano \
			AND p.codigo = c.partido \
			AND c.cargo = ca.codigo \
			AND c.ano = %s \
			AND c.situacao <> '2º TURNO' \
			%s \
			GROUP BY 1,2 \
			ORDER BY SUM(valor) \
			DESC LIMIT 10" % (estado, ano, q)
	executeSQL(SQL)
	
	SQL = "INSERT INTO topPartidos (partido, ano, valor, estado, tipo) \
			SELECT p.codigo, c.ano, SUM(valor), '%s', 'receita' \
			FROM partReceitas c, partido p \
			WHERE p.codigo = c.partido \
			AND c.ano = %s \
			%s \
			GROUP BY 1,2 \
			ORDER BY SUM(valor) \
			DESC LIMIT 10" % (estado, ano, q)
	executeSQL(SQL)
	
	SQL = "INSERT INTO topPartidos (partido, ano, valor, estado, tipo) \
			SELECT p.codigo, c.ano, SUM(valor), '%s', 'despesa' \
			FROM partDespesas c, partido p \
			WHERE p.codigo = c.partido \
			AND c.ano = %s \
			%s \
			GROUP BY 1,2 \
			ORDER BY SUM(valor) \
			DESC LIMIT 10" % (estado, ano, q)
	executeSQL(SQL)

	EXCLUDE = " AND (d.nome NOT LIKE '%partido%'  \
				AND d.nome NOT LIKE '%eleicao%' \
				AND d.nome NOT LIKE '%direcao%' \
				AND d.nome NOT LIKE '%diretorio%') ";
				
	SQL = "INSERT INTO topDoador (doador, ano, estado, valor) \
			SELECT a.codigo, a.ano, a.estado, SUM(vl) FROM \
			( \
				SELECT d.codigo, c.ano, '%s' as estado, SUM(c.valor) as vl \
				FROM candReceitas c, doador d \
				WHERE c.doador = d.codigo \
				AND c.ano = %s \
				%s \
				%s \
				GROUP BY 1,2 \
			UNION ALL \
				SELECT d.codigo, c.ano, '%s' as estado, SUM(c.valor) as vl \
				FROM partReceitas c, doador d \
				WHERE c.doador = d.codigo \
				AND c.ano = %s \
				%s \
				%s \
				GROUP BY 1,2 \
			) a \
			GROUP BY a.codigo, a.ano, a.estado \
			ORDER BY SUM(vl) DESC \
			LIMIT 10" % (estado, ano, EXCLUDE, q, estado, ano, EXCLUDE, q)
	executeSQL(SQL)		
		
	SQL = "INSERT INTO topBens (cpf, ano, estado, valor) \
			SELECT c.cpf, c.ano, c.estado, SUM(b.valor) \
			FROM candBens b, candidato c \
			WHERE c.id = b.id \
			AND c.ano = b.ano \
			AND c.estado = b.estado \
			AND c.cpf = c.cpf \
			AND c.estado = '%s' \
			AND c.ano = '%s' \
			GROUP BY c.cpf, c.ano, c.estado" % (estado, ano)
	executeSQL(SQL)	
	
	return 1

def rendasTop10(anos):
	SQL_QUERY = "SELECT t.cpf \
					FROM topBens t \
					WHERE t.valor is not null \
					GROUP BY t.estado \
					ORDER BY t.valor DESC"
	(result, rowcount) = selectSQL(SQL_QUERY)

	if ( rowcount > 0 and i < len(anos)-1 ):
		for row in result:
			SQL_QUERY2 = "SELECT t.estado, t.cpf, t.ano, t.valor \
							FROM topBens t \
							WHERE t.valor is not null \
							AND t.cpf = '%s' \
							GROUP BY t.estado, t.cpf,t.ano,t.valor \
							ORDER BY t.ano ASC" % (row[1])
			(result2, rowcount2) = selectSQL(SQL_QUERY2)

			if (rowcount2 > 0):
				vl1 = ''
				an1 = ''
				vl2 = ''
				an2 = ''
				est = ''
				cpf = ''
				for i,row1 in enumerate(result2):
					if (i == 0): 
						vl1 = row1[3]
						an1 = row1[2]
					
					an2 = row1[2]
					vl2 = row1[3]
					est = row1[0]
					cpf = row1[1]
					
				if (vl2-vl1 > 0):
					SQL = "INSERT INTO topRendas (cpf, valor, estado, periodo) \
							VALUES ('%s', '%s', '%s', '%s')" % (cpf, vl2-vl1, est, an1+'-'+an2)
					executeSQL(SQL)
	
	return 1

def getFiles(ano):
	dirs = {}
	files = {'consulta_cand_', 'bem_candidato_', 'prestacao_final_', 'consulta_legendas_'}
	dirs['consulta_cand_'] = 'consulta_cand/'
	dirs['bem_candidato_'] = 'bem_candidato/'
	dirs['prestacao_final_'] = 'prestacao_contas/'
	dirs['prestacao_contas_'] = 'prestacao_contas/'
	dirs['consulta_legendas_'] = 'consulta_legendas/'
	
	for file in files:

		#falta de padrao
		if ( ano in anosSemPadrao and file == 'prestacao_final_'):
			file = 'prestacao_contas_'

		if (not os.path.exists(destFolder)):
		    os.makedirs(destFolder)
		    		
		if (not os.path.exists(destFolder + ano)):
		    os.makedirs(destFolder + ano)
		    
		try:
			if (not os.path.isfile(destFolder + ano + '/' + file + ano + '.zip')):
				print "Baixando: " + destFolder + ano + '/' + file + ano + '.zip'
				response = urllib2.urlopen('http://agencia.tse.jus.br/estatistica/sead/odsele/' + dirs[file] + file + ano + '.zip');
				zipcontent= response.read()
			
				with open(destFolder + ano + '/' + file + ano + '.zip', 'w') as f:
					f.write(zipcontent)
			print "Descompactando: " + destFolder + file + ano + '.zip'
			with zipfile.ZipFile(destFolder + ano + '/' + file + ano + '.zip', "r") as z:
				z.extractall(destFolder + ano + '/')
		except urllib2.HTTPError, err:
			print 'File: http://agencia.tse.jus.br/estatistica/sead/odsele/' + dirs[file] + file + ano + '.zip'
			print 'Ocorreu um erro ao processar o arquivo STF: %s' % err.code
			exit(1)
	return 1

def addslashes(string, campo=''):

	string = string.translate(None, "'")
	string = string.translate(None, "\\")
	string = string.translate(None, "\"")
	string = string.replace("/", "-")
	string = string.translate(None, ")")
	string = string.translate(None, "(")
	string = string.translate(None, "?")	
	string = string.translate(None, '¿')
	string = string.translate(None, '´')
	string = string.translate(None, "~")
	string = string.translate(None, "!")
	string = string.translate(None, "|")
	string = string.translate(None, "]")
	string = string.translate(None, "[")
	string = string.translate(None, "*")
	string = string.translate(None, "--")
	string = string.translate(None, "#")
	string = string.translate(None, "\n")
	string = string.translate(None, "\r\n")
	string = string.strip() #trim
	string = string.lstrip() #ltrim
	string = string.rstrip() #rtrim

	if campo == 'nome':
		string = string.translate(None, "´")
		string = string.translate(None, "`")
	
	if (string[:1] == ","):
		string = string[1:]
	if (string[:1] == "."):
		string = string[1:]
	
	if campo == 'nome':
		if string == '':
			return 'NÃO DECLARADO'
		if len(string) < 2:
			return 'ERR'
		if re.search('(?![\d_])\w', string):
			return string
		else:
			return 'ERR'
	
	if campo == 'valor':
		try:
			valor = float(string.replace(',','.'))
			return valor
		except ValueError:
			return 'ERR'
			
	return string

def getCampos(regb, v1, t1, de1, do1):
	
	descricao=''
	doador=''
	tipo=''
	valor=''

	ant=''
	for vx in v1:
		if vx == -1 and ant == '':
			return '0.00'
		try:
			valor = addslashes(regb[vx], 'valor')
			ant = addslashes(regb[vx])
		except IndexError:
			valor = 'ERR'
			pass

		if (valor != 'ERR'):
			break
	
	for dex in de1:
		try:
			descricao = addslashes(regb[dex], 'nome')
		except IndexError:
			descricao = 'ERR'
			pass
			
		if (descricao != 'ERR'):
			descricao = "{0:<10s}".format(truncate(descricao, 200))
			break

	for tx in t1:
		try:
			tipo = "{0:<10s}".format(truncate(regb[tx], 100))
			tipo = buscaCodigo('tipo', addslashes(tipo, 'nome'))
		except IndexError:
			tipo = 'ERR'
			pass
			
		if (tipo != 'ERR'):
			break

	for dox in do1:
		try:
			doador = buscaSimilaridade('doador', addslashes(regb[dox], 'nome'))
		except IndexError:
			doador = 'ERR'
			pass
			
		if (doador != 'ERR'):
			break

	erro=0
	if valor == 'ERR':
		print "VALOR"
		erro=1
	if tipo == 'ERR':
		print "TIPO"
		erro=1
	if descricao == 'ERR':
		print "DESCRICAO"
		erro=1
	if doador == 'ERR':
		print "DOADOR"
		erro=1
	
	if erro == 1:
		for ri,r in enumerate(regb):
			print str(ri)+': '+str(r)
		
		exit(1)
	
	return (valor, tipo, descricao, doador)

def buscaSimilaridade(tabela, chave, ignoreInsert=0):
   
	if (chave == '#NULO'):
		chave = 'NÃO DECLARADO'
	
	chave2 = '%'+chave[:4]+'%'
	
	SQL_QUERY = "SELECT codigo, nome, LEVENSHTEIN(nome, '%s') AS distance FROM %s WHERE nome LIKE '%s' ORDER BY distance ASC LIMIT 1" % (chave, tabela, chave2)
	(result, rowcount) = selectSQL(SQL_QUERY)
   
	print SQL_QUERY
	
	find = 0
	if (rowcount > 0):
		for row in result:
			if row[2] < 30:
				find = 1
				return row[0]
	
	if (find == 0):
		if (ignoreInsert == 0):
			SQL = "INSERT INTO %s VALUES (NULL, '%s')" % (tabela, chave)
			lastId = executeSQL(SQL, '', 1)
			return lastId
		return 0

def buscaCodigo(tabela, chave, ignoreInsert=0):
   
	if (chave == '#NULO'):
		chave = 'NÃO DECLARADO'
		
	SQL_QUERY = "SELECT codigo FROM %s WHERE nome LIKE '%s' LIMIT 1" % (tabela, chave)
	
	(result, rowcount) = selectSQL(SQL_QUERY)
   
	if (rowcount > 0):
		for row in result:
			if row[0] == 'None':
				return 'ERR'
			return row[0]
	
	if (ignoreInsert == 0):
		SQL = "INSERT INTO %s VALUES (NULL, '%s')" % (tabela, chave)
		lastId = executeSQL(SQL, '', 1)
		return lastId
	return 0
		
def buscaPartido(sigla):
   
	SQL_QUERY = "SELECT codigo FROM partido WHERE sigla = '%s'" % (sigla)
	(result, rowcount) = selectSQL(SQL_QUERY)
    
	if (rowcount > 0):
		for row in result:
			return row[0]
	return 0

def getInfoPoliticosORG(numero, estado):
	try:
		url = 'http://politicos.org.br/Parliamentarians/List'
		data = '__RequestVerificationToken=H3DgdvdwJEYvCfAo9t2uRxqaGDj7EMw0z49MCaftno0h5NTJWAVS9NnR-vnbcr6GQhvNcWIK48Wt0AYaamj6vyuhuNBx72f4zw7PxKuot281&Position=&State.Id='+str(numero)+'&Party.Id=&Order=&Name=&CurrentPage=0&PageSize=1000'
		headers = {
				'Cookie': '__RequestVerificationToken=ts9W9va-Bic7ckQTsVuXhrV0G34BUDC8Tcgjyh3GDDDWbgSifxj-geH0Ot7dVZnvQiIxKqN_uAaqFWtAhPYjCZuq2T6fzCkbSwfEqA-zKPg1; _gat_UA-68486425-1=1; _ga=GA1.3.1919473255.1461264679',
				'Origin': 'http://politicos.org.br',
				'Accept-Encoding': 'gzip, deflate',
				'Accept-Language': 'en-US,en;q=0.8',
				'Thunder-Ajax': 'true',
				'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/49.0.2623.108 Chrome/49.0.2623.108 Safari/537.36','Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				'Accept': '*/*',
				'Cache-Control': 'max-age=0',
				'X-Requested-With': 'XMLHttpRequest',
				'Connection': 'keep-alive',
				'Referer': 'http://politicos.org.br/',
				}

		req = urllib2.Request(url, data, headers)
		response = urllib2.urlopen(req)
		buffer = StringIO( response.read())
		html = gzip.GzipFile(fileobj=buffer)
		tags = html.read().split( '<hr class="separa">' )

	except urllib2.HTTPError, err:
		pass
		return
	except urllib2.URLError, err:
		pass
		return
	
	for i,line in enumerate(tags):
		if (i == 0): continue
		import re
		from bs4 import BeautifulSoup
		
		imagem = ''
		sessao = ''
		processo = ''
		
		soup = BeautifulSoup(line, "html.parser")
		sources=soup.findAll('img',{"src":True})
		if (sources):
			imagem = sources[0]['src']

		sources=soup.findAll('ul',{"title":True})
		if (sources):
			nome = sources[0]['title']
			nome = nome.replace(nome[:47], "")
		
		sources=soup.findAll('li',{"class":"pontuacao"},{"class":"pontos"})
		if (sources):
			presenca = str(sources[1])
			presenca = presenca.split('class="pontos"> ')
			presenca = presenca[1].split(" ")
			sessao = presenca[0]
		
		sources=soup.findAll('li',{"class":"pontuacao"},{"class":"pontos"})
		if (sources):
			presenca = str(sources[4])
			presenca = presenca.split('class="pontos"> ')
			presenca = presenca[1].split(" ")
			processo = presenca[0]

		codigo = buscaSimilaridade('candidato', addslashes(nome), 1)
		if (codigo):
			foto = ''
			if (imagem and imagem != '/Content/manager/images/no_photo.png'):
				from PIL import ImageFile
				response = urllib2.urlopen(imagem);
				foto = response.read()
					
			SQL = "UPDATE candidato SET qdeSessoes=%s, qdeProcessos=%s, foto=%s WHERE codigo = %s" 
			ARGS = (sessao, processo, foto, codigo)
			executeSQL(SQL, ARGS)				

	return 1

def cadastraEstados():
	
	for estado in estados:
		SQL_QUERY = "SELECT nome FROM estado WHERE nome = '%s'" % (estado)
		(result, rowcount) = selectSQL(SQL_QUERY)
	
		if (rowcount == 0):
			SQL = "INSERT IGNORE INTO estado VALUES (NULL,'%s')" % (estado)
			executeSQL(SQL)
		
	return 1
		
def partido(ano, estado):

	def legendas():
		SQL_QUERY = "SELECT codigo,sigla,nome FROM partido"
		(result, rowcount) = selectSQL(SQL_QUERY)
		return result
		
	arquivo = destFolder+ano+"/consulta_legendas_"+ano+"_"+estado+".txt"
	try:
		LEG = legendas()
		with open(arquivo, "r") as ins:
			for line in ins:
				regc = line.split(";")
				
				#LEGENDA
				nome = addslashes(regc[13])
				codPartido = addslashes(regc[11])
				sigla = addslashes(regc[12])

				find=0
				for row in LEG:
					if (str(row[0]) == str(codPartido)):
						find=1
					if (find == 1 and str(row[1]) == str(sigla) and str(row[2]) == str(nome)):
						find=2
				
				if (find == 1):
					SQL = "UPDATE partido SET nome = '%s', sigla = '%s' WHERE codigo = %s" % (nome, sigla, codPartido)
					executeSQL(SQL)
					LEG = legendas()
					
				if (find == 0):
					SQL = "INSERT IGNORE INTO partido VALUES ('%s', '%s', '%s')" % (codPartido, sigla, nome)
					executeSQL(SQL)
					LEG = legendas()
		ins.close()

	except IOError:
		print 'Erro ao tentar abrir o arquivo: %s' % arquivo
		if (estado == 'BR' or estado == 'DF'):
			pass
		else:
			exit(1)
		
	return 1
		
def candidato(ano, estado):
	arquivo = destFolder+ano+"/consulta_cand_"+ano+"_"+estado+".txt"
	
	if (ano == '2010'):
		s = 42
	else:
		s = 44
		
	c = 9
	n = 10
	ii = 11
	cc = 13
	p = 25
	pa = 17
	e = 32
	ci = 39

	if (ano == '2012' or ano == '2006' or ano == '2008'):
		s = 42
			
	try:
		with open(arquivo, "r") as ins:
			
			SQL = "SELECT linha FROM candidato c WHERE c.ano = '%s' AND c.estado = '%s'" % (ano, estado)
			(result, rowcount) = selectSQL(SQL)

			linhas = ins.readlines()
			qdeLinhas = len(linhas)
	
			if (rowcount+1 == qdeLinhas): 
				progress(100, 100, str(rowcount+1)+"/"+str(qdeLinhas))
				ins.close()
				return
					
			linhasBanco = [i[0] for i in result]
			linhasFile = [i for i in range(qdeLinhas)]

			diff = list((Counter(linhasFile) - Counter(linhasBanco)).elements())

			ins.seek(0)
			for count,numLinha in enumerate(diff):

				if (numLinha == 0 ): continue

				progress(count, len(diff)-1, str(count)+"/"+str(len(diff)-1))
				
				ins.seek(0)
				regb = linhas[numLinha].split(";")
						
				#LEGENDA
				cargo = buscaSimilaridade('candCargo',addslashes(regb[c]))
				
				nome = addslashes(regb[n], 'nome')
				if (nome == 'ERR'):
					print "NOME"
					exit(1)
					
				id = addslashes(regb[ii])
				cpf = addslashes(regb[cc])
				
				profissao = addslashes(regb[p], 'nome')
				if (profissao == 'ERR'):
					profissao = addslashes(regb[p+1], 'nome')
				if (profissao == 'ERR'):
					print "PROFISSAO+1"
					exit(1)

				profissao = buscaSimilaridade('candProfissao', profissao)

				partido = addslashes(regb[pa])
				if (partido == 'DEFERIDO' or partido == 'INDEFERIDO'):
					partido = addslashes(regb[18])

				if (ano == '2008'):
					partido = buscaPartido(addslashes(regb[18]))
					if (partido == 0):
						partido = buscaPartido(addslashes(regb[19]))
					if (partido == 0):
						partido = buscaPartido(addslashes(regb[20]))

				escolaridade = addslashes(regb[e], 'nome')
				if (escolaridade == 'ERR'):
					escolaridade = addslashes(regb[e+1], 'nome')
				if (escolaridade == 'ERR'):
					print "ESCOLARIDADE+1"
					exit(1)
				escolaridade = buscaSimilaridade('candEscolaridade', escolaridade)
				
				cidade = 'NULL'
				if (regb[ci]):
					cidade = addslashes(regb[ci])
					
				situacao = addslashes(regb[s])
			
				SQL = "INSERT IGNORE INTO candidato (codigo, id, ano, cpf, nome, estado, cidade, profissao, partido, cargo, escolaridade, situacao, linha) VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')" % (id, ano, cpf, nome, estado, cidade, profissao, partido, cargo, escolaridade, situacao, numLinha)
				executeSQL(SQL)
		
		ins.close()

	except IOError:
		print 'Erro ao tentar abrir o arquivo: %s' % arquivo
		if (estado == 'BR' or estado == 'DF'):
			pass
		else:
			exit(1)
		
	return 1

def candBens(ano, estado):
	arquivo = destFolder+ano+"/bem_candidato_"+ano+"_"+estado+".txt"
	
	try:
		with open(arquivo, "r") as ins:

			SQL = "SELECT linha FROM candBens cb WHERE cb.ano = '%s' AND cb.estado = '%s'" % (ano, estado)
			(result, rowcount) = selectSQL(SQL)
			
			linhas = ins.readlines()
			qdeLinhas = len(linhas)
	
			if (rowcount+1 == qdeLinhas): 
				progress(100, 100, str(rowcount+1)+"/"+str(qdeLinhas))
				ins.close()
				return
					
			linhasBanco = [i[0] for i in result]
			linhasFile = [i for i in range(qdeLinhas)]

			diff = list((Counter(linhasFile) - Counter(linhasBanco)).elements())

			ins.seek(0)
			for count,numLinha in enumerate(diff):

				if (numLinha == 0 ): continue

				progress(count, len(diff)-1, str(count)+"/"+str(len(diff)-1))
				
				ins.seek(0)
				regb = linhas[numLinha].split(";")
			
				cpf = ''

				#LEGENDA
				ano = addslashes(regb[2])
				id = addslashes(regb[5])
				
				(valor, tipo, descricao, doador) = getCampos(regb, [9], [7], [8, 7], [])

				SQL = "SELECT codigo FROM candidato WHERE id = %s" % (id)
				(r, c) = selectSQL(SQL)
				if (c > 0):
					SQL = "INSERT IGNORE INTO candBens VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s',  '%s')" % (id, ano, estado, valor, tipo, descricao, numLinha)
					executeSQL(SQL)

		ins.close()

	except IOError:
		print 'Erro ao tentar abrir o arquivo: %s' % arquivo
		if (estado == 'BR' or estado == 'DF'):
			pass
		else:
			exit(1)
		
	return 1

def candDespesas(ano, estado):
	arquivo = destFolder+ano+"/despesas_candidatos_"+ano+"_"+estado+".txt"
	t1 = [20, 21, 22]
	v1 = [19, 20, 21]
	n = 4
	d1 = [15]
		
	if (ano == '2010'):
		arquivo = destFolder+ano+"/candidato/"+estado+"/DespesasCandidatos.txt"
		t1 = [15, 16]
		v1 = [14, 15]
		n = 1
		d1 = [18, 16]
	
	if (ano == '2012'):
		t1 = [15, 18, 15]
		v1 = [18, 17, 19]
		n = 1
		d1 = [13, 14]

	SQL = "SELECT linha FROM candDespesas cd WHERE cd.ano = '%s' AND cd.estado = '%s'" % (ano, estado)

	if (ano == '2006'):
		t1 = [11]
		v1 = [9]
		n = 0
		d1 = [18, 16, 19, 22]

	if (ano == '2008'):
		t1 = [13]
		v1 = [11]
		n = 0
		d1 = [20, 21, 18, 24, 25, 26]

	if (ano == '2006' or ano == '2008'):
		arquivo = destFolder+ano+"/prestacao_contas_"+ano+"/"+ano+"/Candidato/Despesa/DespesaCandidato.csv"
		SQL = "SELECT linha FROM candDespesas cd WHERE cd.ano = '%s'" % (ano)
		
	try:
		with open(arquivo, "r") as ins:
		
			(result, rowcount) = selectSQL(SQL)
			
			linhas = ins.readlines()
			qdeLinhas = len(linhas)

			if (rowcount+1 == qdeLinhas): 
				progress(100, 100, str(rowcount+1)+"/"+str(qdeLinhas))
				ins.close()
				return
			
			linhasBanco = [i[0] for i in result]
			linhasFile = [i for i in range(qdeLinhas)]

			diff = list((Counter(linhasFile) - Counter(linhasBanco)).elements())

			ins.seek(0)
			for count,numLinha in enumerate(diff):

				if (numLinha == 0 ): continue

				progress(count, len(diff)-1, str(count)+"/"+str(len(diff)-1))
				
				ins.seek(0)
				regb = linhas[numLinha].split(";")

				if (ano in ('2006', '2008')):
					estado = addslashes(regb[5])
					
				id = addslashes(regb[n])
				
				(valor, tipo, descricao, doador) = getCampos(regb, v1, t1, d1, [])

				
				SQL = "INSERT IGNORE INTO candDespesas VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s')" % (id, ano, estado, valor, tipo, descricao, numLinha)

				executeSQL(SQL)
				
		ins.close()

	except IOError:
		print 'Erro ao tentar abrir o arquivo: %s' % arquivo
		if (estado == 'BR' or estado == 'DF'):
			pass
		else:
			exit(1)
	
	return 1

def partDespesas(ano, estado):
	arquivo = destFolder+ano+"/despesas_partidos_"+ano+"_"+estado+".txt"
	s = 7
	t1 = [17]
	v1 = [16, 17]
	d1 = [11, 12]
		
	if (ano == '2010'):
		arquivo = destFolder+ano+"/partido/"+estado+"/DespesasPartidos.txt"
		s = 3
		t1 = [11]
		v1 = [9]
		d1 = [10]
		
	if (ano == '2012'):
		s = 6
		t1 = [16]
		v1 = [15, 16, 14]
		d1 = [10, 11, 12]
		
	try:
		with open(arquivo, "r") as ins:
			
			SQL = "SELECT pd.linhaPartido FROM partDespesas pd WHERE pd.ano = '%s' AND pd.estado = '%s' AND pd.linhaPartido IS NOT NULL" % (ano, estado)
			(result, rowcount) = selectSQL(SQL)
			
			linhas = ins.readlines()
			qdeLinhas = len(linhas)

			if (rowcount+1 == qdeLinhas): 
				progress(100, 100, str(rowcount+1)+"/"+str(qdeLinhas))
				ins.close()
				return
					
			linhasBanco = [i[0] for i in result]
			linhasFile = [i for i in range(qdeLinhas)]

			diff = list((Counter(linhasFile) - Counter(linhasBanco)).elements())

			ins.seek(0)
			for count,numLinha in enumerate(diff):

				if (numLinha == 0 ): continue

				progress(count, len(diff)-1, str(count)+"/"+str(len(diff)-1))
				
				ins.seek(0)
				regb = linhas[numLinha].split(";")
				
				#LEGENDA
				partido = buscaPartido(addslashes(regb[s]))
				
				(valor, tipo, descricao, doador) = getCampos(regb, v1, t1, d1, [])
					
				SQL = "INSERT IGNORE INTO partDespesas (codigo, partido, ano, estado, valor, tipo, descricao, linhaPartido) VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s')" % (partido, ano, estado, valor, tipo, descricao, numLinha)
				executeSQL(SQL)

		ins.close()

	except IOError:
		print 'Erro ao tentar abrir o arquivo: %s' % arquivo
		if (estado == 'BR' or estado == 'DF'):
			pass
		else:
			exit(1)
	
	return 1

def comiDespesas(ano, estado):
	arquivo = destFolder+ano+"/despesas_comites_"+ano+"_"+estado+".txt"
	s = 7
	t1 = [17, 16]
	v1 = [16, 17]
	d1 = [11, 12]

	if (ano == '2010'):
		arquivo = destFolder+ano+"/comite/"+estado+"/DespesasComites.txt"
		s = 3
		t1 = [11]
		v1 = [9]
		d1 = [10]

	if (ano == '2012'):
		s = 6
		t1 = [16]
		v1 = [15, 16, 14]
		d1 = [10, 11]

	SQL = "SELECT pd.linhaComite FROM partDespesas pd WHERE pd.ano = '%s' AND pd.estado = '%s' AND pd.linhaComite IS NOT NULL" % (ano, estado)

	if (ano == '2006'):
		s = 2
		t1 = [7]
		v1 = [5]
		d1 = [14, 15, 12]

	if (ano == '2008'):
		s = 2
		t1 = [9]
		v1 = [7]
		d1 = [16, 14, 17, 23, 22, 21]
		
	if (ano == '2006' or ano == '2008'):
		arquivo = destFolder+ano+"/prestacao_contas_"+ano+"/"+ano+"/Comitê/Despesa/DespesaComitê.CSV"
		SQL = "SELECT pd.linhaComite FROM partDespesas pd WHERE pd.ano = '%s' AND pd.linhaComite IS NOT NULL" % (ano)
		
	try:
		with open(arquivo, "r") as ins:
			
			(result, rowcount) = selectSQL(SQL)
			
			linhas = ins.readlines()
			qdeLinhas = len(linhas)

			if (rowcount+1 == qdeLinhas): 
				progress(100, 100, str(rowcount+1)+"/"+str(qdeLinhas))
				ins.close()
				return
					
			linhasBanco = [i[0] for i in result]
			linhasFile = [i for i in range(qdeLinhas)]

			diff = list((Counter(linhasFile) - Counter(linhasBanco)).elements())

			ins.seek(0)
			for count,numLinha in enumerate(diff):

				if (numLinha == 0 ): continue

				progress(count, len(diff)-1, str(count)+"/"+str(len(diff)-1))
				
				ins.seek(0)
				regb = linhas[numLinha].split(";")

				if (ano in ('2006', '2008')):
					estado = addslashes(regb[3])

				#LEGENDA
				partido = buscaPartido(addslashes(regb[s]))

				(valor, tipo, descricao, doador) = getCampos(regb, v1, t1, d1, [])
					
				SQL = "INSERT IGNORE INTO partDespesas (codigo, partido, ano, estado, valor, tipo, descricao, linhaComite) VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s')" % (partido, ano, estado, valor, tipo, descricao, numLinha)
				executeSQL(SQL)

		ins.close()

	except IOError:
		print 'Erro ao tentar abrir o arquivo: %s' % arquivo
		if (estado == 'BR' or estado == 'DF'):
			pass
		else:
			exit(1)
	
	return 1
	
def candReceitas(ano, estado):
	arquivo = destFolder+ano+"/receitas_candidatos_"+ano+"_"+estado+".txt"
	t1 = [23, 24]
	v1 = [22, 23]
	n = 4
	d1 = [15, 14]
	
	if (ano == '2010'):
		arquivo = destFolder+ano+"/candidato/"+estado+"/ReceitasCandidatos.txt"
		t1 = [15, 16]
		v1 = [14, 15]
		n = 1
		d1 = [12, 16]

	if (ano == '2012'):
		t1 = [15, 18]
		v1 = [17]
		n = 1
		d1 = [13, 14]

	SQL = "SELECT linha FROM candReceitas cr WHERE cr.ano = '%s' AND cr.estado = '%s' " % (ano, estado)

	if (ano == '2006'):
		t1 = [11]
		v1 = [9, -1]
		n = 0
		d1 = [15]
		e = 3
		
	if (ano == '2008'):
		t1 = [16]
		v1 = [14]
		n = 0
		d1 = [20]
		e = 6
		
	if (ano == '2006' or ano == '2008'):
		arquivo = destFolder+ano+"/prestacao_contas_"+ano+"/"+ano+"/Candidato/Receita/ReceitaCandidato.csv"
		SQL = "SELECT linha FROM candReceitas cr WHERE cr.ano = '%s' " % (ano)

	try:
		with open(arquivo, "r") as ins:
		
			(result, rowcount) = selectSQL(SQL)

			linhas = ins.readlines()
			qdeLinhas = len(linhas)
			
			if (rowcount+1 == qdeLinhas): 
				progress(100, 100, str(rowcount+1)+"/"+str(qdeLinhas))
				ins.close()
				return
					
			linhasBanco = [i[0] for i in result]
			linhasFile = [i for i in range(qdeLinhas)]

			diff = list((Counter(linhasFile) - Counter(linhasBanco)).elements())

			ins.seek(0)
			for count,numLinha in enumerate(diff):

				if (numLinha == 0 ): continue

				progress(count, len(diff)-1, str(count)+"/"+str(len(diff)-1))
				
				ins.seek(0)
				regb = linhas[numLinha].split(";")

				if (ano in ('2006', '2008')):
					estado = addslashes(regb[e])

				(valor, tipo, descricao, doador) = getCampos(regb, v1, t1, [], d1)

				id = addslashes(regb[n])

				SQL = "INSERT IGNORE INTO candReceitas VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s')" % (id, ano, estado, valor, tipo, doador, numLinha)
				executeSQL(SQL)

		ins.close()

	except IOError:
		print 'Erro ao tentar abrir o arquivo: %s' % arquivo
		if (estado == 'BR' or estado == 'DF'):
			pass
		else:
			exit(1)

	return 1

def partReceitas(ano, estado):
	arquivo = destFolder+ano+"/receitas_partidos_"+ano+"_"+estado+".txt"
	p = 14
	t1 = [21]
	d1 = [28, 11, 12]
	v1 = [19, 20]
		
	if (ano == '2010'):
		arquivo = destFolder+ano+"/partido/"+estado+"/ReceitasPartidos.txt"
		p = 3
		t1 = [10]
		d1 = [7]
		v1 = [9]

	if (ano == '2012'):
		p = 6
		t1 = [20, 12]
		d1 = [10, 11]
		v1 = [18, 19, 14]

	if (ano == '2014'):
		p = 7

	try:
		with open(arquivo, "r") as ins:
		
			SQL = "SELECT pr.linhaPartido FROM partReceitas pr WHERE pr.ano = '%s' AND pr.estado = '%s' AND pr.linhaPartido IS NOT NULL" % (ano, estado)
			(result, rowcount) = selectSQL(SQL)
			
			linhas = ins.readlines()
			qdeLinhas = len(linhas)

			if (rowcount+1 == qdeLinhas): 
				progress(100, 100, str(rowcount+1)+"/"+str(qdeLinhas))
				ins.close()
				return
					
			linhasBanco = [i[0] for i in result]
			linhasFile = [i for i in range(qdeLinhas)]

			diff = list((Counter(linhasFile) - Counter(linhasBanco)).elements())

			ins.seek(0)
			for count,numLinha in enumerate(diff):

				if (numLinha == 0 ): continue

				progress(count, len(diff)-1, str(count)+"/"+str(len(diff)-1))
				
				ins.seek(0)
				regb = linhas[numLinha].split(";")

				if regb[0] == '"Data e hora"': continue
					
				#LEGENDA
				partido = addslashes(regb[p])
				if (ano == '2010' or ano == '2012' or ano == '2014'): 
					partido = buscaPartido(addslashes(regb[p]))

				(valor, tipo, descricao, doador) = getCampos(regb, v1, t1, [], d1)

				SQL = "INSERT IGNORE INTO partReceitas (codigo, partido, ano, estado, valor, tipo, doador, linhaPartido) VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s')" % (partido, ano, estado, valor, tipo, doador, numLinha)

				executeSQL(SQL)

		ins.close()

	except IOError:
		print 'Erro ao tentar abrir o arquivo: %s' % arquivo
		if (estado == 'BR' or estado == 'DF'):
			pass
		else:
			exit(1)

	return 1

def comiReceitas(ano, estado):
	arquivo = destFolder+ano+"/receitas_comites_"+ano+"_"+estado+".txt"
	p = 7
	t1 = [21]
	d1 = [28, 11, 12]
	v1 = [19, 20]
		
	if (ano == '2010'):
		arquivo = destFolder+ano+"/comite/"+estado+"/ReceitasComites.txt"
		p = 3
		t1 = [10]
		d1 = [7]
		v1 = [9]

	if (ano == '2012'):
		p = 6
		t1 = [19, 15]
		d1 = [10, 11]
		v1 = [18, 14]

	SQL = "SELECT pr.linhaComite FROM partReceitas pr WHERE pr.ano = '%s' AND pr.estado = '%s' AND pr.linhaComite IS NOT NULL" % (ano, estado)

	if (ano == '2006'):
		p = 1
		t1 = [7]
		d1 = [11]
		v1 = [5]
		e = 3

	if (ano == '2008'):
		p = 1
		t1 = [9]
		d1 = [13]
		v1 = [7]
		e = 3
		
	if (ano == '2006' or ano == '2008'):
		arquivo = destFolder+ano+"/prestacao_contas_"+ano+"/"+ano+"/Comitê/Receita/ReceitaComitê.CSV"
		SQL = "SELECT pr.linhaComite FROM partReceitas pr WHERE pr.ano = '%s' AND pr.linhaComite IS NOT NULL" % (ano)
				
	try:
		with open(arquivo, "r") as ins:
		
			(result, rowcount) = selectSQL(SQL)
			
			linhas = ins.readlines()
			qdeLinhas = len(linhas)

			if (rowcount+1 == qdeLinhas): 
				progress(100, 100, str(rowcount+1)+"/"+str(qdeLinhas))
				ins.close()
				return
					
			linhasBanco = [i[0] for i in result]
			linhasFile = [i for i in range(qdeLinhas)]

			diff = list((Counter(linhasFile) - Counter(linhasBanco)).elements())
						
			ins.seek(0)
			for count,numLinha in enumerate(diff):

				if (numLinha == 0): continue

				progress(count, len(diff)-1, str(count)+"/"+str(len(diff)-1))
				
				ins.seek(0)
				regb = linhas[numLinha].split(";")
		
				if (ano in ('2006', '2008')):
					estado = addslashes(regb[e])

				#LEGENDA
				partido = addslashes(regb[p])
				if (ano == '2010' or ano == '2012' or ano == '2014'): 
					partido = buscaPartido(addslashes(regb[p]))

				if partido == 'NULO':
					for ri,r in enumerate(regb):
						print str(ri)+': '+str(r)
					exit(1)
				(valor, tipo, descricao, doador) = getCampos(regb, v1, t1, [], d1)

				SQL = "INSERT IGNORE INTO partReceitas (codigo, partido, ano, estado, valor, tipo, doador, linhaComite) VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s')" % (partido, ano, estado, valor, tipo, doador, numLinha)
				executeSQL(SQL)

		ins.close()

	except IOError:
		print 'Erro ao tentar abrir o arquivo: %s' % arquivo
		if (estado == 'BR' or estado == 'DF'):
			pass
		else:
			exit(1)

	return 1

# MAIN: inicio do programa
def main():
	global inicio
	anos = []
	change = 0

	connect()
	cadastraEstados()
		
	choice = ''
	if (len(sys.argv) > 1):
		if (sys.argv[1] == 'POL'):
			choice = 'POL'
		if (sys.argv[1] != 'POL'):
			choice = 'ano'
			inicio = sys.argv[1]
			
	SQL_QUERY = "SELECT MAX(ano) as ano FROM anoEleitoral"
	(result, rowcount) = selectSQL(SQL_QUERY)
	if (rowcount > 0):
		if (result[0][0] != None):
			for row in result:
				if int(row[0]) >= int(inicio):
					inicio = int(row[0]) + 2

	if (choice == 'POL'):
		for i,estado in enumerate(estados):
			print "\nProcessando Informacoes do Politicos.org: " + estado
			getInfoPoliticosORG(i+1, estado)
		exit(1)
	
	while (inicio):
		try:
			url = 'http://www.tse.jus.br/hotSites/pesquisas-eleitorais/resultados_anos/'+str(inicio)+'.html'
			req = urllib2.Request(url)
			response = urllib2.urlopen(req)
			print "Limpando Tabelas TOP 10"
			deleteTop10(str(inicio))
			print 'Processando ANO: '+str(inicio)
			procAno(str(inicio))
			anos.append(str(inicio))
			SQL = "INSERT anoEleitoral (ano) VALUES ('%s')" % (inicio)
			executeSQL(SQL)
			if (choice == 'ano'):
				inicio = 0
			else:
				inicio += 2
				change = 1
		except urllib2.HTTPError, err:
			inicio = 0
	
	if change:
		SQL = "DELETE FROM topRendas"
		executeSQL(SQL)
		print "\nCriando Tabela Rendas TOP 10"
		rendasTop10(anos)
	
	disconnect()
			
def procAno(ano):

	#Download dos arquivos no site do STE
	#getFiles(ano)

	for i,estado in enumerate(estados):
		print "\n\nProcessando Legendas: " + ano + "|" + estado
		partido(ano, estado)
		print "Processando Candidatos: " + ano + "|" + estado
		candidato(ano, estado)
		print "\nProcessando Bens: " + ano + "|" + estado
		candBens(ano, estado)

		if (ano not in ('2006', '2008')):
			print "\nProcessando Despesas Candidatos: " + ano + "|" + estado
			candDespesas(ano, estado)
			print "\nProcessando Despesas Partidos: " + ano + "|" + estado
			partDespesas(ano, estado)
			print "\nProcessando Despesas Comites: " + ano + "|" + estado
			comiDespesas(ano, estado)

			print "\nProcessando Receitas Candidatos: " + ano + "|" + estado
			candReceitas(ano, estado)
			print "\nProcessando Receitas Partidos: " + ano + "|" + estado
			partReceitas(ano, estado)
			print "\nProcessando Receitas Comites: " + ano + "|" + estado
			comiReceitas(ano, estado)
		
	if (ano in ('2006', '2008')):
		print "\nProcessando Despesas Candidatos: " + ano
		candDespesas(ano, '')
		print "\nProcessando Despesas Comites: " + ano
		comiDespesas(ano, '')

		print "\nProcessando Receitas Candidatos: " + ano
		candReceitas(ano, '')
		print "\nProcessando Receitas Comites: " + ano
		comiReceitas(ano, '')

	for i,estado in enumerate(estados):
		print "\nProcessando Informacoes do Politicos.org: " + ano + "|" + estado
		getInfoPoliticosORG(i+1, estado)
		
		print "\nCriando Tabelas TOP 10: " + ano + "|" + estado
		createTop10(ano, estado)
		print "\nEstado finalizado: " + estado
		
	print "\nCriando Tabelas TOP 10: " + ano + "|BR"
	createTop10(ano, 'BR')
	
	print "\nScript finalizado."

if __name__ == '__main__':
    main()
