
"""This has been really helpful. Here is my implementation for any given table:"""
def sql_replace(self, tableobject, dictargs):

    #missing check of table object is valid
    primarykeys = [key.name for key in inspect(tableobject).primary_key]

    filterargs = []
    for primkeys in primarykeys:
        if dictargs[primkeys] is not None:
            filterargs.append(getattr(db.RT_eqmtvsdata, primkeys) == dictargs[primkeys])
        else:
            return

    ##query = select([db.RT_eqmtvsdata]).where(and_(*filterargs))
    query = select([tableobject]).where(and_(*filterargs))

    if self.r_ExecuteAndErrorChk2(query)[primarykeys[0]] is not None:
        # update
        filter = and_(*filterargs)
        query = tableobject.__table__.update().values(dictargs).where(filter)
        return self.w_ExecuteAndErrorChk2(query)

    else:
        query = tableobject.__table__.insert().values(dictargs)
        return self.w_ExecuteAndErrorChk2(query)

# example usage
inrow = {'eqmtvs_id': eqmtvsid, 'datetime': dtime, 'param_id': paramid}

self.sql_replace(tableobject=db.RT_eqmtvsdata, dictargs=inrow)