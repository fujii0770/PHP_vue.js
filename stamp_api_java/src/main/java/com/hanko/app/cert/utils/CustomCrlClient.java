package com.hanko.app.cert.utils;

import java.io.ByteArrayOutputStream;
import java.io.InputStream;
import java.security.cert.X509Certificate;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.core.io.ClassPathResource;
import org.springframework.core.io.Resource;

import com.itextpdf.signatures.CrlClientOnline;
import com.itextpdf.signatures.ICrlClient;

/**
 * An implementation of the CrlClient that fetches the CRL bytes from an URL or get from file.
 *
 * @author Hoapx
 */
public class CustomCrlClient extends CrlClientOnline {

	/**
	 * The Logger instance.
	 */
	private static final Logger LOGGER = LoggerFactory.getLogger(CustomCrlClient.class);

	/**
	 * The URLs of the CRLs.
	 */
	protected Map<String, String> offlineUrls = new HashMap<String, String>();

	/**
	 * The URLs of the CRLs.
	 */
	protected List<byte[]> offlineContents;

	/**
	 * Creates a CustomCrlClient instance that will try to find a single CRL by
	 * walking through the certificate chain.
	 */
	public CustomCrlClient(Map<String, String> offlineUrls) {
		this.offlineUrls = offlineUrls;
	}

	/**
	 * Fetches the CRL bytes from an URL. If no url is passed as parameter, the url
	 * will be obtained from the certificate. If you want to load a CRL from a local
	 * file, subclass this method and pass an URL with the path to the local file to
	 * this method. An other option is to use the CrlClientOffline class.
	 *
	 * @see ICrlClient#getEncoded(java.security.cert.X509Certificate,
	 *      java.lang.String)
	 */
	@Override
	public Collection<byte[]> getEncoded(X509Certificate checkCert, String url) {
		if (checkCert == null)
			return null;
		if (offlineContents != null && offlineContents.size() > 0) {
			return offlineContents;
		}else if (offlineContents == null) {
			offlineContents = new ArrayList<>();
	        for (Map.Entry<String, String> entry : offlineUrls.entrySet()) {
            	String crlUrl = entry.getKey();
                String crlFile = entry.getValue();
	            try {
	                LOGGER.info("Checking CRL: " + crlUrl);
	                Resource resource = new ClassPathResource(crlFile);
	                InputStream inp = resource.getInputStream();

	                byte[] buf = new byte[1024];
	                ByteArrayOutputStream bout = new ByteArrayOutputStream();
	                while (true) {
	                    int n = inp.read(buf, 0, buf.length);
	                    if (n <= 0)
	                        break;
	                    bout.write(buf, 0, n);
	                }
	                inp.close();
	                offlineContents.add(bout.toByteArray());
	                LOGGER.info("Added CRL found at: " + crlUrl);
	            } catch (Exception e) {
	                LOGGER.info("Skipped CRL: " + e.getMessage() + " for " + crlUrl);
	            }
	        }
	        return offlineContents;
		}else {
			return super.getEncoded(checkCert, url);
		}
	}
}
