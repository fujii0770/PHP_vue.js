package com.hanko.app.service;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.util.ArrayList;

import com.hanko.app.bean.StampRequest;
import com.hanko.app.bean.TextRequest;

public interface IPdfSignatureService {

	public boolean impressPdf(ArrayList<TextRequest> texts, ArrayList<StampRequest> stamps, InputStream inputStream, OutputStream outStream, InputStream appendedInputStream);

	public boolean signaturePdf(boolean isUsingTas, InputStream inputStream, OutputStream outStream, InputStream keyStream, String password, String reason);

	public boolean enableLtv(boolean isUsingTas, InputStream inputStream, OutputStream outStream) throws IOException;

	public boolean impressWithSignaturePdf(ArrayList<TextRequest> texts, ArrayList<StampRequest> stamps, InputStream inputStream, OutputStream outputStream, InputStream keyStream, String password, String reason, boolean isUsingTas);

	public boolean signatureDTS(InputStream inputStream, OutputStream outStream, String reason) throws IOException;
}
