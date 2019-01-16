import networkx as nx

G = nx.read_edgelist("networks.txt", create_using=nx.DiGraph) 
pr = nx.pagerank(G, alpha=0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight',dangling=None) 
vals = []
with open("pagerank.txt", 'w') as fopen:
    for key in pr:
        vals.append(pr[key])
        fopen.write("/home/zhaoyanl/share/latimes/latimes/" + key + "=" + str(pr[key]) + "\n")

vals = sorted(vals)
print vals[len(vals) - 1]
