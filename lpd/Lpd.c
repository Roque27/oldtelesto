#include <stdio.h>
#include <unistd.h>
#include <dirent.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <fcntl.h>
#include <time.h>
#include <strings.h>
#include <sys/file.h>

char NombreDeLogDeLpd[512];
char Mensaje[512];
int  NivelDeDetalleDelSistema;
FILE *fLog;
int  fLock;
FILE *hPrinter;

extern int errno;

#define NombreDelArchivoIni "/lpd/Lpd.ini"

char Nombre_De_La_Cola[512];
char cTamanio_En_Bytes[64];
unsigned char Buffer[2048];
char Servidor_Local[512];
char Propietario_Del_Listado[512];
char NombreDelTrabajo[512];
char Titulo_Del_Listado[512];
char Host_Name[512];
char Nombre_Del_Cliente[512];
char DirectorioTemporal[512];
char ArchivoTemporal[512];
char PathArchivoTemporal[512];
char PostProceso[512];
char Device[512];
char iniName[255];
char Tipo_Mensaje[2];
long nBytes;
int  i;
long Tamanio_En_Bytes;
char Header[512];
int  nHeader;

char *ValorEnIni(char Seccion[], char Elemento[]) {
	static char Valor[128];

	FILE *fIni;
	char buf[2048];
	int  EstoyEnLaSeccion;
	int  p;
	char *p1;

	bzero(Valor, sizeof Valor);
	EstoyEnLaSeccion=0;
	// fIni=fopen(NombreDelArchivoIni, "r");
	fIni=fopen(iniName, "r");

	for(;;) {
		if(fgets(buf, sizeof buf, fIni)==NULL) {
			break;
		}
		buf[strlen(buf)-1]=0;
		if(buf[0]=='[') {
			buf[strlen(buf)-1]=0;
			if(strcmp(&buf[1], Seccion)==0)
				EstoyEnLaSeccion=1;
			else
				EstoyEnLaSeccion=0;
		} else {
			if(EstoyEnLaSeccion==1) {
				p1=(char *)strchr(buf, '=');
				if(p1!=0) {
					p=(int)(p1-&buf[0]);
					if(strncmp(buf, Elemento, p)==0) {
						strcpy(Valor, &buf[p+1]);
						break;
					}
				}
			}
	   }
	}
	fclose(fIni);
	return(Valor);
}

char *GetTmpName(){

    char* filename;
    struct tm* tm;
	struct timeval detail_time;

    time_t now;
    now = time(0); 
    tm = localtime(&now); 
    filename = (char*) malloc (255);
	gettimeofday(&detail_time,NULL);
	sprintf(filename, "%04d%02d%02d%02d%02d%02d%d", tm->tm_year + 1900, tm->tm_mon+1, 
			tm->tm_mday, tm->tm_hour, tm->tm_min, tm->tm_sec, detail_time.tv_usec /1000);
	
	return filename;
}

Espera(char *Device) {
	char lockFile[128];
	bzero(lockFile, sizeof lockFile);
	sprintf(lockFile, "/lpd/%s.LCK", &Device[5]);
	fLock=open(lockFile, O_RDWR);
	lockf(fLock, F_LOCK, 1);
}

int main(int argc, char *argv[]) {


	
	int n;
	char command[512];

	setregid(3, 3); /* Me convierto en grupo sys */
	setreuid(0, 0); /* Me convierto en usuario root */

	if(argc != 2) {
		fprintf(stderr, "Uso: lpd archivo.ini\n");
		return 1;
	} else {
		strcpy(iniName, argv[1]);
	}
	
	NivelDeDetalleDelSistema=atoi(ValorEnIni("General", "Nivel_De_Log"));
	strcpy(NombreDeLogDeLpd, ValorEnIni("General", "Log"));
	strcpy(DirectorioTemporal, ValorEnIni("General", "Directorio_Temporal"));
	strcpy(PostProceso, ValorEnIni("General", "PostProceso"));

	fLog=fopen(NombreDeLogDeLpd, "a");

	
	strcpy(PathArchivoTemporal, DirectorioTemporal);
	strcpy(ArchivoTemporal, GetTmpName());
	strcat(PathArchivoTemporal, ArchivoTemporal);

	fprintf(fLog, "##############################################################\n");
	fprintf(fLog, "######## Archivo temporal: %s ########\n", PathArchivoTemporal);
	fprintf(fLog, "##############################################################\n");
	fflush(fLog);
	
	bzero(Nombre_De_La_Cola, sizeof Nombre_De_La_Cola);
	bzero(cTamanio_En_Bytes, sizeof cTamanio_En_Bytes);
	bzero(Servidor_Local, sizeof Servidor_Local);
	bzero(Nombre_Del_Cliente, sizeof Nombre_Del_Cliente);
	bzero(Propietario_Del_Listado, sizeof Propietario_Del_Listado);
	bzero(NombreDelTrabajo, sizeof NombreDelTrabajo);
	bzero(Titulo_Del_Listado, sizeof Titulo_Del_Listado);
	bzero(Host_Name, sizeof Host_Name);
	bzero(Device, sizeof Device);

	
	for(;;) {
		bzero(Tipo_Mensaje, sizeof Tipo_Mensaje);
		nBytes=recv(0, Tipo_Mensaje, 1, 0);
		if(Tipo_Mensaje[0]==2) {
			for(i=0;;) {
				recv(0, &Nombre_De_La_Cola[i], 1, 0);
				if(Nombre_De_La_Cola[i]==10) {
					Nombre_De_La_Cola[i]=0;
					break;
				}
				i++;
			}
			Buffer[0]=0;
			send(0, Buffer, 1, 0);
		} else {
			if(Tipo_Mensaje[0]==3) {
				// Tamanio_En_Bytes
				for(i=0;;) {
					recv(0, &cTamanio_En_Bytes[i], 1, 0);
					if(cTamanio_En_Bytes[i]==' ') {
						cTamanio_En_Bytes[i]=0;
						break;
					}
					i++;
				}
				Tamanio_En_Bytes=atol(cTamanio_En_Bytes);
				recv(0, Buffer, 3, 0); // dfA
				recv(0, Buffer, 3, 0); // Identificador
				// Servidor_Local
				for(i=0;;) {
					recv(0, &Servidor_Local[i], 1, 0);
					if(Servidor_Local[i]==10) {
						Servidor_Local[i]=0;
						break;
					}
					i++;
				}
				strcpy(Device, ValorEnIni(Nombre_De_La_Cola, "Device"));
				// Espera(Device);
				fprintf(fLog, "Recibiendo %d bytes desde %s (%s)\n", Tamanio_En_Bytes, Nombre_De_La_Cola, Device);
				fflush(fLog);

				// hPrinter=open(Device, O_RDWR);
				hPrinter=fopen(PathArchivoTemporal, "wb");
				fprintf(fLog, "%s opened\n", PathArchivoTemporal);
				fflush(fLog);
				
				Buffer[0]=0;
				send(0, Buffer, 1, 0);
				
				// Texto del listado
				for(; Tamanio_En_Bytes>0; Tamanio_En_Bytes--) {
					recv(0, Buffer, 1, 0);
					for(;;) {
						if(fwrite(&Buffer[0], 1, 1, hPrinter)==1) {
							break;
						}
					}
				}
				
				Buffer[0]=0;
				recv(0, Buffer, 1, 0);
				Buffer[0]=0;
				send(0, Buffer, 1, 0);
				recv(0, Buffer, 1, 0); // 2

				bzero(cTamanio_En_Bytes, sizeof cTamanio_En_Bytes);
				// Tamanio_En_Bytes
				for(i=0;;) {
					recv(0, &cTamanio_En_Bytes[i], 1, 0);
					if(cTamanio_En_Bytes[i]==' ') {
						cTamanio_En_Bytes[i]=0;
						break;
					}
					i++;
				}
				Tamanio_En_Bytes=atol(cTamanio_En_Bytes);

				// cfA000
				recv(0, Buffer, 6, 0); 
				
				// Servidor_Local
				for(i=0;;) {
					recv(0, &Servidor_Local[i], 1, 0);
					if(Servidor_Local[i]==10) {
						Servidor_Local[i]=0;
						break;
					}
					i++;
				}

				Buffer[0]=0;
				send(0, Buffer, 1, 0);
				recv(0, Buffer, Tamanio_En_Bytes, 0);

				fprintf(fLog, "Buffer: \n %s \n", Buffer);
				fflush(fLog);
				
				nHeader=0;
				for(i=0; i<Tamanio_En_Bytes; i++) {
					if(Buffer[i]==10) {
						Header[nHeader]=0;
						switch(Header[0]) {
							case 'P': // owner del listado
							  strcpy(Propietario_Del_Listado, &Header[1]);
							  break;
							case 'J': // Job name
							  strcpy(NombreDelTrabajo, &Header[1]);
							  break;
							case 'T': // Titulo del listado
							  strcpy(Titulo_Del_Listado, &Header[1]);
							  break;
							case 'H': // Host name
							  strcpy(Host_Name, &Header[1]);
							  break;
						}
						nHeader=0;
					} else {
						Header[nHeader++]=Buffer[i];
					}
				}
				recv(0, Buffer, 1, 0);
				break;
			}
		}
	}
	
	
	Buffer[0]=0;
	send(0,Buffer,1,0);
	fprintf(fLog, "Fin \n");
	fflush(fLog);
							
	if(strcmp(Titulo_Del_Listado, "") == 0) {
		strcpy(Titulo_Del_Listado, "SinTitulo");
	}
	if(strcmp(Propietario_Del_Listado, "") == 0) {
		strcpy(Propietario_Del_Listado, "SinPropietario");
	}
	if(strcmp(Host_Name, "") == 0) {
		strcpy(Host_Name, Servidor_Local);
	}
	
	fprintf(fLog, "Nombre_De_La_Cola: %s \n", Nombre_De_La_Cola);
	fprintf(fLog, "cTamanio_En_Bytes: %s \n", cTamanio_En_Bytes);
	fprintf(fLog, "Servidor_Local: %s \n", Servidor_Local);
	fprintf(fLog, "Nombre_Del_Cliente: %s \n", Nombre_Del_Cliente);
	fprintf(fLog, "Propietario_Del_Listado: %s \n", Propietario_Del_Listado);
	fprintf(fLog, "NombreDelTrabajo: %s \n", NombreDelTrabajo);
	fprintf(fLog, "Titulo_Del_Listado: %s \n", Titulo_Del_Listado);
	fprintf(fLog, "HostName: %s \n", Host_Name);
	fprintf(fLog, "Device: %s \n", Device);  
	fflush(fLog);
	
	// Preparar el postproceso
	sprintf(command, "%s %s \"%s\" 1 %s %s %s %s >> %s", PostProceso, Propietario_Del_Listado, Titulo_Del_Listado, ArchivoTemporal, PathArchivoTemporal,Host_Name , cTamanio_En_Bytes, NombreDeLogDeLpd);
	fprintf(fLog, "Ejecutando: %s \n", command);  
	fflush(fLog);
	
	fclose(hPrinter);
	close(0);
	// Ejecutar el post proceso
	system(command);
	
	fprintf(fLog, "##############################################################\n");
	fprintf(fLog, "############ Finaliza: %s ############\n", PathArchivoTemporal);
	fprintf(fLog, "##############################################################\n");
	fflush(fLog);
 }

