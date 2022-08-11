## Stamp API

## Steps to Setup

**1. Clone the repository**

```bash
git clone https://git-codecommit.ap-northeast-1.amazonaws.com/v1/repos/BOX-API
cd hanko/stamp_api_java
```

**2. Run the app**

Type the following command from the root directory of the project to run it -

```bash
mvn spring-boot:run
```

Alternatively, you can package the application in the form of a JAR file and then run it like so -

```bash
mvn clean package
java -jar target/stamp_api-0.0.1-SNAPSHOT.jar
```

Default the application will run in port 8080