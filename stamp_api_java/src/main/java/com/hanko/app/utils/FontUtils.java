package com.hanko.app.utils;

import java.io.IOException;
import java.io.UncheckedIOException;
import java.util.HashMap;
import java.util.Map;
import java.util.function.Function;
import java.util.stream.Collectors;

import com.itextpdf.io.font.PdfEncodings;
import com.itextpdf.kernel.font.PdfFont;
import com.itextpdf.kernel.font.PdfFontFactory;

public class FontUtils {
	public static Map<String, String> FONTS = new HashMap<String, String>();

	static {
		FONTS.put("Meiryo", "fonts/meiryo.ttc");
		FONTS.put("MS Gothic", "fonts/msgothic.ttc");
		FONTS.put("MS Mincho", "fonts/msmincho.ttc");
		FONTS.put("shnkgo", "fonts/SHnkgo5.ttc");
		FONTS.put("shnmin", "fonts/SHnmin5.ttc");
		FONTS.put("shgyo", "fonts/SHgyo5.ttc");
	}

	public static Map<String, PdfFont> createFonts() {
		return FONTS
				.keySet()
				.stream()
				.collect(Collectors.toMap(Function.identity(), name -> {
					try {
						return PdfFontFactory.createRegisteredFont(name, PdfEncodings.IDENTITY_H);
					} catch (IOException e) {
						throw new UncheckedIOException(e);
					}
				}));
	}

}
