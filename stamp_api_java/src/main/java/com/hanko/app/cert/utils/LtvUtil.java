package com.hanko.app.cert.utils;

import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.PublicKey;
import java.security.cert.CRLException;
import java.security.cert.CertificateException;
import java.security.cert.CertificateFactory;
import java.security.cert.X509CRL;
import java.security.cert.X509Certificate;

import org.bouncycastle.asn1.ASN1EncodableVector;
import org.bouncycastle.asn1.ASN1Enumerated;
import org.bouncycastle.asn1.ASN1InputStream;
import org.bouncycastle.asn1.ASN1ObjectIdentifier;
import org.bouncycastle.asn1.ASN1OctetString;
import org.bouncycastle.asn1.ASN1Primitive;
import org.bouncycastle.asn1.ASN1Sequence;
import org.bouncycastle.asn1.ASN1TaggedObject;
import org.bouncycastle.asn1.DEROctetString;
import org.bouncycastle.asn1.DERSequence;
import org.bouncycastle.asn1.DERTaggedObject;
import org.bouncycastle.asn1.ocsp.BasicOCSPResponse;
import org.bouncycastle.asn1.ocsp.OCSPObjectIdentifiers;
import org.bouncycastle.asn1.x509.Extension;
import org.bouncycastle.cert.X509CertificateHolder;
import org.bouncycastle.cert.jcajce.JcaX509CertificateConverter;
import org.bouncycastle.cert.ocsp.BasicOCSPResp;
import org.bouncycastle.cert.ocsp.OCSPException;
import org.bouncycastle.jce.provider.BouncyCastleProvider;
import org.bouncycastle.operator.ContentVerifierProvider;
import org.bouncycastle.operator.OperatorCreationException;
import org.bouncycastle.operator.jcajce.JcaContentVerifierProviderBuilder;

import com.itextpdf.io.font.PdfEncodings;
import com.itextpdf.io.source.ByteBuffer;
import com.itextpdf.kernel.pdf.PdfName;
import com.itextpdf.kernel.pdf.PdfString;
import com.itextpdf.signatures.PdfSignature;

/**
 * <a href=
 * "https://stackoverflow.com/questions/51370965/how-can-i-add-pades-ltv-using-itext">
 * how can I add PAdES-LTV using itext </a> <br/>
 * <a href=
 * "https://stackoverflow.com/questions/51639464/itext7-ltvverification-addverification-not-enabling-ltv">
 * iText7 LtvVerification.addVerification not enabling LTV </a>
 * <p>
 * This class adds LTV information to a signed PDF to make it LTV enabled as
 * reported by Adobe Acrobat.
 * </p>
 * <p>
 * It has originally been written for iText 5 in the context of the former
 * question. In the context of the latter one it has been ported to iText 7. As
 * a side effect some iText-5-isms may be contained in this code.
 * </p>
 *
 * @author mkl
 */
public class LtvUtil {
	//
	// VRI signature hash key calculation
	//
	public static PdfName getCrlHashKey(byte[] crlBytes)
			throws NoSuchAlgorithmException, IOException, CRLException, CertificateException {
		CertificateFactory cf = CertificateFactory.getInstance("X.509");
		X509CRL crl = (X509CRL) cf.generateCRL(new ByteArrayInputStream(crlBytes));
		byte[] signatureBytes = crl.getSignature();
		DEROctetString octetString = new DEROctetString(signatureBytes);
		byte[] octetBytes = octetString.getEncoded();
		byte[] octetHash = hashBytesSha1(octetBytes);
		PdfName octetName = new PdfName(convertToHex(octetHash));
		return octetName;
	}

	public static PdfName getOcspHashKey(byte[] basicResponseBytes) throws NoSuchAlgorithmException, IOException {
		BasicOCSPResponse basicResponse = BasicOCSPResponse.getInstance(basicResponseBytes);
		byte[] signatureBytes = basicResponse.getSignature().getBytes();
		DEROctetString octetString = new DEROctetString(signatureBytes);
		byte[] octetBytes = octetString.getEncoded();
		byte[] octetHash = hashBytesSha1(octetBytes);
		PdfName octetName = new PdfName(convertToHex(octetHash));
		return octetName;
	}

	public static PdfName getSignatureHashKey(PdfSignature sig) throws NoSuchAlgorithmException, IOException {
		PdfString contents = sig.getContents();
		byte[] bc = PdfEncodings.convertToBytes(contents.getValue(), null);
		if (PdfName.ETSI_RFC3161.equals(sig.getSubFilter())) {
			try (ASN1InputStream din = new ASN1InputStream(new ByteArrayInputStream(bc))) {
				ASN1Primitive pkcs = din.readObject();
				bc = pkcs.getEncoded();
			}
		}
		byte[] bt = hashBytesSha1(bc);
		return new PdfName(convertToHex(bt));
	}

	public static byte[] hashBytesSha1(byte[] b) throws NoSuchAlgorithmException {
		MessageDigest sh = MessageDigest.getInstance("SHA1");
		return sh.digest(b);
	}

	public static String convertToHex(byte[] bytes) {
		ByteBuffer buf = new ByteBuffer();
		for (byte b : bytes) {
			buf.appendHex(b);
		}
		return PdfEncodings.convertToString(buf.toByteArray(), null).toUpperCase();
	}

	//
	// OCSP response helpers
	//
	public static X509Certificate getOcspSignerCertificate(byte[] basicResponseBytes)
			throws CertificateException, OperatorCreationException, OCSPException {
		JcaX509CertificateConverter converter = new JcaX509CertificateConverter()
				.setProvider(BouncyCastleProvider.PROVIDER_NAME);
		BasicOCSPResponse borRaw = BasicOCSPResponse.getInstance(basicResponseBytes);
		BasicOCSPResp bor = new BasicOCSPResp(borRaw);

		for (final X509CertificateHolder x509CertificateHolder : bor.getCerts()) {
			X509Certificate x509Certificate = converter.getCertificate(x509CertificateHolder);

			JcaContentVerifierProviderBuilder jcaContentVerifierProviderBuilder = new JcaContentVerifierProviderBuilder();
			jcaContentVerifierProviderBuilder.setProvider(BouncyCastleProvider.PROVIDER_NAME);
			final PublicKey publicKey = x509Certificate.getPublicKey();
			ContentVerifierProvider contentVerifierProvider = jcaContentVerifierProviderBuilder.build(publicKey);

			if (bor.isSignatureValid(contentVerifierProvider))
				return x509Certificate;
		}

		return null;
	}

	public static byte[] buildOCSPResponse(byte[] BasicOCSPResponse) throws IOException {
		DEROctetString doctet = new DEROctetString(BasicOCSPResponse);
		ASN1EncodableVector v2 = new ASN1EncodableVector();
		v2.add(OCSPObjectIdentifiers.id_pkix_ocsp_basic);
		v2.add(doctet);
		ASN1Enumerated den = new ASN1Enumerated(0);
		ASN1EncodableVector v3 = new ASN1EncodableVector();
		v3.add(den);
		v3.add(new DERTaggedObject(true, 0, new DERSequence(v2)));
		DERSequence seq = new DERSequence(v3);
		return seq.getEncoded();
	}

	public static String getCACURL(X509Certificate certificate) {
		ASN1Primitive obj;
		try {
			obj = getExtensionValue(certificate, Extension.authorityInfoAccess.getId());
			if (obj == null) {
				return null;
			}
			ASN1Sequence AccessDescriptions = (ASN1Sequence) obj;
			for (int i = 0; i < AccessDescriptions.size(); i++) {
				ASN1Sequence AccessDescription = (ASN1Sequence) AccessDescriptions.getObjectAt(i);
				if (AccessDescription.size() != 2) {
					continue;
				} else if (AccessDescription.getObjectAt(0) instanceof ASN1ObjectIdentifier) {
					ASN1ObjectIdentifier id = (ASN1ObjectIdentifier) AccessDescription.getObjectAt(0);
					if ("1.3.6.1.5.5.7.48.2".equals(id.getId())) {
						ASN1Primitive description = (ASN1Primitive) AccessDescription.getObjectAt(1);
						String AccessLocation = getStringFromGeneralName(description);
						if (AccessLocation == null) {
							return "";
						} else {
							return AccessLocation;
						}
					}
				}
			}
		} catch (IOException e) {
			return null;
		}
		return null;
	}

	public static ASN1Primitive getExtensionValue(X509Certificate certificate, String oid) throws IOException {
		byte[] bytes = certificate.getExtensionValue(oid);
		if (bytes == null) {
			return null;
		}
		ASN1InputStream aIn = new ASN1InputStream(new ByteArrayInputStream(bytes));
		ASN1OctetString octs = (ASN1OctetString) aIn.readObject();
		aIn = new ASN1InputStream(new ByteArrayInputStream(octs.getOctets()));
		return aIn.readObject();
	}

	public static String getStringFromGeneralName(ASN1Primitive names) throws IOException {
		ASN1TaggedObject taggedObject = (ASN1TaggedObject) names;
		return new String(ASN1OctetString.getInstance(taggedObject, false).getOctets(), "ISO-8859-1");
	}
}