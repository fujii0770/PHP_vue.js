package com.hanko.app;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.EnableAutoConfiguration;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.boot.autoconfigure.jdbc.DataSourceAutoConfiguration;
import org.springframework.boot.autoconfigure.orm.jpa.HibernateJpaAutoConfiguration;
import org.springframework.util.ResourceUtils;

import com.hanko.app.utils.FontUtils;
import com.itextpdf.kernel.font.PdfFontFactory;
import com.itextpdf.licensekey.LicenseKey;

@SpringBootApplication
@EnableAutoConfiguration(exclude={DataSourceAutoConfiguration.class,HibernateJpaAutoConfiguration.class})
public class StampApi {
	private static String fontFolder = "/home/centos/stamp_api/";

	public static void write2ExternalFonts() {
		ClassLoader classLoader = StampApi.class.getClassLoader();
		for (Map.Entry<String, String> font : FontUtils.FONTS.entrySet()) {
			try {
				Path fontFile = Paths.get(fontFolder + font.getValue());
				if (!fontFile.toFile().exists()) {
					Files.copy(classLoader.getResourceAsStream(font.getValue()), fontFile, StandardCopyOption.REPLACE_EXISTING);
				}
				Logger.getLogger(StampApi.class.getName()).log(Level.INFO, "Wrote fonts.");
			} catch (IOException e) {
				Logger.getLogger(StampApi.class.getName()).log(Level.SEVERE, e.getMessage(), e);
			}
		}
	}

	public static void registerExternalFonts() {
		for (Map.Entry<String, String> font : FontUtils.FONTS.entrySet()) {
			try {
				File fontFile = ResourceUtils.getFile(fontFolder + font.getValue());
				PdfFontFactory.register(fontFile.getAbsolutePath(), font.getKey());
				Logger.getLogger(StampApi.class.getName()).log(Level.INFO, "Registered fonts.");
			} catch (FileNotFoundException e) {
				Logger.getLogger(StampApi.class.getName()).log(Level.SEVERE, e.getMessage(), e);
			}
		}
	}

	public static void main(String[] args) {
		SpringApplication.run(StampApi.class, args);
		LicenseKey.loadLicenseFile(StampApi.class.getClassLoader().getResourceAsStream("itextkey1534903188467_0.xml"));
		write2ExternalFonts();
		registerExternalFonts();
	}
}