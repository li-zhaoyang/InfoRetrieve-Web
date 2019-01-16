import java.io.*;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;
import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;


public class Main {
    public static void main(String[] argv) {
        String dirPath = "C:\\Users\\lizha\\Documents\\Courses\\CSCI572\\HW4\\share\\latimes\\latimes\\";
        String csvFilePath = "C:\\Users\\lizha\\Documents\\Courses\\CSCI572\\HW4\\share\\URLtoHTML_latimes.csv";
        Map<String, String> fileUrlMap = new HashMap<>();
        Map<String, String> urlFileMap = new HashMap<>();
        try {
            BufferedReader br = new BufferedReader(new FileReader(new File(csvFilePath)));
            String st;
            while ((st = br.readLine()) != null) {
                String[] arr = st.split(",");
                fileUrlMap.put(arr[0], arr[1]);
                urlFileMap.put(arr[1], arr[0]);
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
        File dir =  new File(dirPath);
        Set<String> edges = new HashSet();
        for (File file: dir.listFiles()) {
            try {
                System.out.println(file.getName());
                Document doc = Jsoup.parse(file, "UTF-8", fileUrlMap.get(file.getName()));
                Elements links = doc.select("a[href]");
                Elements pngs = doc.select("[src]");
                for (Element link: links) {
                    String url = link.attr("href").trim();
                    if (urlFileMap.containsKey(url)) {
                        edges.add(file.getName() + " " + urlFileMap.get(url));
                    }
                }
            } catch (Exception e) {
                e.printStackTrace();
            }

        }
        String networkXFileName = "networks.txt";
        try {
            BufferedWriter writer = new BufferedWriter(new FileWriter(networkXFileName));
            for (String s: edges) {
                writer.write(s + '\n');
            }
            writer.close();
        } catch (Exception e) {
            e.printStackTrace();
        }

    }
}
