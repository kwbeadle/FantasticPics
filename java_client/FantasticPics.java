import java.net.*;
import java.util.*;
import java.io.*;

public class FantasticPics {
  public static HttpURLConnection con = null;
  public static BufferedReader stdIn = new BufferedReader(new InputStreamReader(System.in));
  public static String host = "http://www.server.com/fantasticpics.php";
  public static String user = "user";
  public static String pass = "abcdef";
  public static String crlf = "\r\n";
  public static String twoHyphens = "--";
  public static String boundary =  "*****";
  public static int maxBufferSize = 1024 * 4;

  public static void proc_upload_cmd() {
	String userInput;
	String filePathPrefix = "";
	String targetDir;
	File myFile;
	long fileCount, fileLen;
	int bytesAvailable;
	List<String> fileList = new ArrayList<String>();
    try {
      System.out.print("Please enter source file or directory: ");
      userInput = stdIn.readLine();
      myFile = new File (userInput);
      System.out.print("Please enter destination directory: ");
      targetDir = stdIn.readLine();
      fileCount = 0;
	  fileList.clear();
	  if (myFile.isFile()) {
        fileCount = 1;
        filePathPrefix = myFile.getPath();
        fileList.add(myFile.getName());
      } else if (myFile.isDirectory()) {
        filePathPrefix = myFile.getPath();
        Collections.addAll(fileList, myFile.list());
        fileCount = myFile.list().length;
      }
      for (int i = 0; i < fileCount; i++) {
        if (fileCount == 1) {
	      myFile = new File(filePathPrefix);
        } else {
          myFile = new File(filePathPrefix + "\\" + fileList.get(i));
        }
        URL url = new URL(host);
        con = (HttpURLConnection) url.openConnection();
        con.setRequestMethod("POST");
        con.setRequestProperty("Connection", "Keep-Alive");
        con.setRequestProperty("Cache-Control", "no-cache");
        con.setRequestProperty("Content-Type", "multipart/form-data;boundary=" + boundary);
        con.setDoInput(true);
        con.setDoOutput(true);
      	con.setUseCaches(false);
      	DataOutputStream dos = new DataOutputStream(con.getOutputStream());
      	dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"USER\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + user + crlf);             	 
      	dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"PASS\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + pass + crlf);             	 
      	dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"CMD\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + "STOR" + crlf);             	 
      	dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"SRC\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + "client" + crlf);             	 
      	
      	// Read file into buffer
        FileInputStream fis = new FileInputStream(myFile);
      	fileLen = bytesAvailable = fis.available();
    	  byte[] inputBuf = new byte[bytesAvailable];
        int bytesRead = fis.read(inputBuf, 0, bytesAvailable);
      	dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"FILE\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + targetDir + "/" + myFile.getName() + crlf);             	 
      	dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"file_contents\"; filename=\"" + myFile.getName() + "\"" + crlf + "Content-Type: multipart/form-data" + crlf + crlf);             	 
        while (bytesRead > 0) {
          dos.write(inputBuf, 0, bytesAvailable);
          bytesAvailable = fis.available();
          bytesAvailable = Math.min(bytesAvailable, maxBufferSize);
          bytesRead = fis.read(inputBuf, 0, bytesAvailable);
        }
        fis.close();
        dos.writeBytes(crlf + twoHyphens + boundary + twoHyphens + crlf);
        dos.flush();
      	dos.close();
  	
      // Read the response
      DataInputStream dis = new DataInputStream(con.getInputStream());
      ByteArrayOutputStream baos = new ByteArrayOutputStream();
      byte[] bytes = new byte[maxBufferSize];
      int bytesRead2;
      while ((bytesRead2 = dis.read(bytes)) != -1) {
        baos.write(bytes, 0, bytesRead2);
      }
      byte[] bytesReceived = baos.toByteArray();
      baos.close();
      dis.close();
      String response = new String(bytesReceived);
      System.out.println(response);
    }
	} catch (UnknownHostException e) {
	  System.err.println("Don't know about host: " + host + "\n" + e);
	} catch (IOException e) {
	  System.err.println("Couldn't get I/O for the connection to: " + host + "\n" + e);
	}
  }
  
  
  public static void proc_list_cmd() {
	String userInput;
	try {
	  System.out.print("Please enter target directory: ");
	  userInput = stdIn.readLine();
	  URL url = new URL(host);
	  con = (HttpURLConnection) url.openConnection();
	  con.setRequestMethod("POST");
	  con.setRequestProperty("Connection", "Keep-Alive");
	  con.setRequestProperty("Cache-Control", "no-cache");
	  con.setRequestProperty("Content-Type", "multipart/form-data;boundary=" + boundary);
	  con.setDoInput(true);
	  con.setDoOutput(true);
	  con.setUseCaches(false);
	  DataOutputStream dos = new DataOutputStream(con.getOutputStream());
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"USER\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + user + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"PASS\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + pass + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"CMD\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + "NLST" + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"SRC\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + "client" + crlf);
      dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"FILE\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + userInput + crlf);             	 
	  dos.flush();
	  dos.close();
	  // Read the response
	  DataInputStream dis = new DataInputStream(con.getInputStream());
	  ByteArrayOutputStream baos = new ByteArrayOutputStream();
	  byte[] bytes = new byte[maxBufferSize];
	  int bytesRead2;
	  while ((bytesRead2 = dis.read(bytes)) != -1) {
	    baos.write(bytes, 0, bytesRead2);
	  }
	  byte[] bytesReceived = baos.toByteArray();
	  baos.close();
	  dis.close();
	  String response = new String(bytesReceived);
	  response = response.replaceAll("<br/>", "\n");
	  System.out.println("\n" + response);
	} catch (UnknownHostException e) { 
	  System.err.println("Don't know about host: " + host + "\n" + e);
	} catch (IOException e) {
      System.err.println("Couldn't get I/O for the connection to: " + host + "\n" + e);
    }
  }
  
  public static void proc_delete_cmd() {
	String userInput;
	try {
	  System.out.print("Please enter target file: ");
	  userInput = stdIn.readLine();
	  URL url = new URL(host);
	  con = (HttpURLConnection) url.openConnection();
	  con.setRequestMethod("POST");
	  con.setRequestProperty("Connection", "Keep-Alive");
	  con.setRequestProperty("Cache-Control", "no-cache");
	  con.setRequestProperty("Content-Type", "multipart/form-data;boundary=" + boundary);
	  con.setDoInput(true);
	  con.setDoOutput(true);
	  con.setUseCaches(false);
	  DataOutputStream dos = new DataOutputStream(con.getOutputStream());
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"USER\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + user + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"PASS\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + pass + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"CMD\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + "DELE" + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"SRC\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + "client" + crlf);
      dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"FILE\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + userInput + crlf);             	 
	  dos.flush();
	  dos.close();
	  // Read the response
	  DataInputStream dis = new DataInputStream(con.getInputStream());
	  ByteArrayOutputStream baos = new ByteArrayOutputStream();
	  byte[] bytes = new byte[maxBufferSize];
	  int bytesRead2;
	  while ((bytesRead2 = dis.read(bytes)) != -1) {
	    baos.write(bytes, 0, bytesRead2);
	  }
	  byte[] bytesReceived = baos.toByteArray();
	  baos.close();
	  dis.close();
	  String response = new String(bytesReceived);
	  System.out.println(response);
	} catch (UnknownHostException e) { 
	  System.err.println("Don't know about host: " + host + "\n" + e);
	} catch (IOException e) {
      System.err.println("Couldn't get I/O for the connection to: " + host + "\n" + e);
    }
  }
  
  public static void proc_rmd_cmd() {
	String userInput;
	try {
	  System.out.print("Please enter target directory: ");
	  userInput = stdIn.readLine();
	  URL url = new URL(host);
	  con = (HttpURLConnection) url.openConnection();
	  con.setRequestMethod("POST");
	  con.setRequestProperty("Connection", "Keep-Alive");
	  con.setRequestProperty("Cache-Control", "no-cache");
	  con.setRequestProperty("Content-Type", "multipart/form-data;boundary=" + boundary);
	  con.setDoInput(true);
	  con.setDoOutput(true);
	  con.setUseCaches(false);
	  DataOutputStream dos = new DataOutputStream(con.getOutputStream());
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"USER\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + user + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"PASS\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + pass + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"CMD\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + "RMD" + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"SRC\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + "client" + crlf);
      dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"FILE\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + userInput + crlf);             	 
	  dos.flush();
	  dos.close();
	  // Read the response
	  DataInputStream dis = new DataInputStream(con.getInputStream());
	  ByteArrayOutputStream baos = new ByteArrayOutputStream();
	  byte[] bytes = new byte[maxBufferSize];
	  int bytesRead2;
	  while ((bytesRead2 = dis.read(bytes)) != -1) {
	    baos.write(bytes, 0, bytesRead2);
	  }
	  byte[] bytesReceived = baos.toByteArray();
	  baos.close();
	  dis.close();
	  String response = new String(bytesReceived);
	  System.out.println(response);
	} catch (UnknownHostException e) { 
	  System.err.println("Don't know about host: " + host + "\n" + e);
	} catch (IOException e) {
      System.err.println("Couldn't get I/O for the connection to: " + host + "\n" + e);
    }
  }
  
  public static void proc_sync_cmd() {
	try {
	  URL url = new URL(host);
	  con = (HttpURLConnection) url.openConnection();
	  con.setRequestMethod("POST");
	  con.setRequestProperty("Connection", "Keep-Alive");
	  con.setRequestProperty("Cache-Control", "no-cache");
	  con.setRequestProperty("Content-Type", "multipart/form-data;boundary=" + boundary);
	  con.setDoInput(true);
	  con.setDoOutput(true);
	  con.setUseCaches(false);
	  DataOutputStream dos = new DataOutputStream(con.getOutputStream());
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"USER\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + user + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"PASS\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + pass + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"CMD\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + "SYNC" + crlf);             	 
	  dos.writeBytes(twoHyphens + boundary + crlf + "Content-Disposition: form-data; name=\"SRC\"" + crlf + "Content-Type: text/plain; charset=UTF-8" + crlf + crlf + "client" + crlf);
	  dos.flush();
	  dos.close();
	  // Read the response
	  DataInputStream dis = new DataInputStream(con.getInputStream());
	  ByteArrayOutputStream baos = new ByteArrayOutputStream();
	  byte[] bytes = new byte[maxBufferSize];
	  int bytesRead2;
	  while ((bytesRead2 = dis.read(bytes)) != -1) {
	    baos.write(bytes, 0, bytesRead2);
	  }
	  byte[] bytesReceived = baos.toByteArray();
	  baos.close();
	  dis.close();
	  String response = new String(bytesReceived);
	  System.out.println(response);
	} catch (UnknownHostException e) { 
	  System.err.println("Don't know about host: " + host + "\n" + e);
	} catch (IOException e) {
      System.err.println("Couldn't get I/O for the connection to: " + host + "\n" + e);
    }
  }
  
  public static void main(String[] args) {
	String userInput;
    try {
      System.out.print(host + "> ");
      while ((userInput = stdIn.readLine()) != null) {
        if (userInput.equalsIgnoreCase("quit")) {
          break;
        } else if (userInput.equalsIgnoreCase("upload")) {
          proc_upload_cmd();
        } else if (userInput.equalsIgnoreCase("list")) {
          proc_list_cmd();
        } else if (userInput.equalsIgnoreCase("delete")) {
        	proc_delete_cmd();
        } else if (userInput.equalsIgnoreCase("rmd")) {
        	proc_rmd_cmd();
        } else if (userInput.equalsIgnoreCase("sync")) {
        	proc_sync_cmd();
        } else if (userInput.equalsIgnoreCase("set host")) {
            System.out.print("Please enter host url: ");
            host = stdIn.readLine();
        } else if (userInput.equalsIgnoreCase("set user")) {
            System.out.print("Please enter username: ");
            user = stdIn.readLine();
            System.out.print("Please enter password: ");
            pass = stdIn.readLine();
        }
        System.out.print(host + "> ");
      }
      con.disconnect();
    } catch (UnknownHostException e) {
	    System.err.println("Don't know about host: " + host + "\n" + e);
    } catch (IOException e) {
	    System.err.println("Couldn't get I/O for the connection to: " + host + "\n" + e);
    }
  }
}

