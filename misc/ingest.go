package main

import (
	"bytes"
	"crypto/rand"
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"fmt"
	//   "bufio"
	"io"
	"io/ioutil"
	"log"
	"net"
	"net/http"
	"net/url"
	"os"
	"strconv"
	"time"
)

const (
	ConnHost = "0.0.0.0"
	ConnType = "tcp"
	//ListenPort   = "80"
	DataEndpoint = "https://ai.anime.technology/api/v1/CreateEvent"
)

func main() {

	l, err := net.Listen(ConnType, ConnHost+":"+os.Getenv("PORT"))

	if err != nil {
		fmt.Println("Error listening:", err.Error())
		os.Exit(1)
	}

	defer l.Close()
	fmt.Println("Listening on " + ConnHost + ":" + os.Getenv("PORT"))
	for {
		// listen for incoming connections
		conn, err := l.Accept()
		if err != nil {
			fmt.Println(err.Error())
			os.Exit(1)
		}
		// goroutine
		go handleRequest(conn)
	}
}

func handleRequest(conn net.Conn) {
	for {
		remoteip, remoteport, err := net.SplitHostPort(conn.RemoteAddr().String())
		if err != nil {
			fmt.Println(err)
			return
		}
		fmt.Println("Received inbound connection from " + remoteip + ":" + remoteport)
		/*
		   s := bufio.NewScanner(f)
		   s.Split(bufio.ScanLines)

		   for s.Scan() {
		       fmt.Println(s.Text())
		   }
		*/
		/*
		   data, _, err := bufio.ScanLines(conn, true)
		   if err != nil {
		       fmt.Println(err)
		       return
		   }
		*/
		//	while(bufio.NewReader(conn).ReadLine())
		var buf bytes.Buffer
		_, _ = io.Copy(&buf, conn)
		fmt.Println("total size:", buf.Len())
		//print(buf.String())
		data := buf.String()
		//fmt.Println("aaaa")
		//_ = remoteip
		//_ = remoteport

		// fmt.Println("Data: " + data)
		snitch(remoteip, remoteport, data)

		_ = conn.Close()
		return
	}

}

func generateSignature(currentTime int64, expiresTime int64, nonce string) (response string) {

	stringToEncode := "/api/v1/CreateEvent" + os.Getenv("TOKENID") + "|" + os.Getenv("TOKEN") + "|" + strconv.FormatInt(currentTime, 10) +
		"|" + strconv.FormatInt(expiresTime, 10) + "|" + nonce
	//fmt.Println(stringToEncode)
	hash := sha256.New()
	hash.Write([]byte(stringToEncode))
	hexHash := hex.EncodeToString(hash.Sum(nil))

	return hexHash
}

func generateHex(n int) (string, error) {
	b := make([]byte, n)
	if _, err := rand.Read(b); err != nil {
		return "", err
	}
	return hex.EncodeToString(b), nil
}

func snitch(ip string, port string, data string) {
	endpoint, err := url.Parse(DataEndpoint)
	if err != nil {
		log.Fatal(err)
	}
	q := endpoint.Query()
	currentTime := time.Now().Unix()
	//fmt.Println(currentTime)
	expiresTime := currentTime + 60

	nonce, _ := generateHex(64)

	q.Set("id", os.Getenv("TOKENID"))

	q.Set("time", strconv.FormatInt(currentTime, 10))

	q.Set("expires", strconv.FormatInt(expiresTime, 10))

	q.Set("signature", generateSignature(currentTime, expiresTime, nonce))

	q.Set("nonce", nonce)

	endpoint.RawQuery = q.Encode()

	fmt.Println("Await: Send report with IP " + ip + " and port " + port)
	// fmt.Println(q)

	array := map[string]string{"ip": ip, "src_port": port, "dest_port": os.Getenv("PORT"), "report_type": "TCP_DATA", "info": data}
	payload, _ := json.Marshal(array)

	req, err := http.NewRequest("POST", endpoint.String(), bytes.NewBuffer(payload))
	req.Header.Add("Content-Type", "application/json")

	client := &http.Client{}
	resp, err := client.Do(req)
	fmt.Print("HTTP request sent, awaiting response... ")
	if err != nil {
		fmt.Println(err)
		return
	}
	defer resp.Body.Close()

	fmt.Println(resp.Status)
	//fmt.Println(resp.Header)
	body, _ := ioutil.ReadAll(resp.Body)
	_ = body
	fmt.Println(string(body))

}
